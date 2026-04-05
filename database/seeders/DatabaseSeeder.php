<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\AvailabilitySlot;
use App\Models\Faq;
use App\Models\Permission;
use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissionMap = [
            'dashboard' => ['view'],
            'portfolio' => ['view', 'create', 'update', 'delete'],
            'service' => ['view', 'create', 'delete'],
            'booking' => ['view', 'update', 'verify-payment', 'export'],
            'report' => ['view'],
            'backup' => ['view', 'create', 'import'],
            'recycle' => ['view', 'restore'],
            'access' => ['view', 'update'],
            'user' => ['view', 'create', 'update', 'delete', 'reset-password'],
            'activity' => ['view'],
            'testimonial' => ['view', 'create', 'delete'],
            'faq' => ['view', 'create', 'delete'],
            'setting' => ['view', 'update'],
        ];

        $allPermissions = collect();
        foreach ($permissionMap as $module => $actions) {
            foreach ($actions as $action) {
                $slug = $module . '.' . $action;
                $allPermissions->push(
                    Permission::firstOrCreate(
                        ['slug' => $slug],
                        ['name' => Str::headline($slug), 'module' => $module]
                    )
                );
            }
        }

        $superadmin = Role::firstOrCreate(
            ['slug' => 'superadmin'],
            ['name' => 'Superadmin', 'description' => 'Akses penuh', 'is_system' => true]
        );

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'description' => 'Akses operasional utama', 'is_system' => true]
        );

        $staff = Role::firstOrCreate(
            ['slug' => 'staff'],
            ['name' => 'Staff', 'description' => 'Akses terbatas operasional', 'is_system' => true]
        );

        $superadmin->permissions()->sync($allPermissions->pluck('id')->all());
        $admin->permissions()->sync($allPermissions->whereNotIn('slug', ['access.update', 'user.delete'])->pluck('id')->all());
        $staff->permissions()->sync($allPermissions->whereIn('slug', [
            'dashboard.view',
            'booking.view',
            'booking.update',
            'booking.verify-payment',
            'activity.view',
        ])->pluck('id')->all());

        User::firstOrCreate(
            ['email' => 'superadmin@mua.local'],
            [
                'role_id' => $superadmin->id,
                'name' => 'Superadmin MUA',
                'phone' => '081234567890',
                'password' => Hash::make('Admin12345!'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $categories = ['Wedding', 'Wisuda', 'Engagement', 'Party', 'Prewedding', 'Editorial'];
        foreach ($categories as $index => $name) {
            PortfolioCategory::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $index]
            );
        }

        $serviceCategories = ['Bridal Package', 'Graduation Makeup', 'Party Makeup', 'Photoshoot Makeup'];
        foreach ($serviceCategories as $index => $name) {
            ServiceCategory::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $index]
            );
        }

        $serviceSamples = [
            ['name' => 'Wedding Makeup + Hairdo', 'price' => 3500000, 'duration' => 240, 'home_fee' => 500000],
            ['name' => 'Wisuda Makeup Natural Glam', 'price' => 850000, 'duration' => 120, 'home_fee' => 150000],
            ['name' => 'Engagement Makeup Elegan', 'price' => 1250000, 'duration' => 150, 'home_fee' => 250000],
            ['name' => 'Party Makeup Soft Glam', 'price' => 700000, 'duration' => 90, 'home_fee' => 100000],
        ];

        foreach ($serviceSamples as $index => $item) {
            $category = ServiceCategory::orderBy('id')->skip($index % 4)->first();
            Service::firstOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'service_category_id' => $category?->id,
                    'name' => $item['name'],
                    'description' => 'Paket lengkap dengan detail makeup premium dan finishing tahan lama.',
                    'duration_minutes' => $item['duration'],
                    'price' => $item['price'],
                    'is_home_service_available' => true,
                    'home_service_fee' => $item['home_fee'],
                    'is_active' => true,
                ]
            );
        }

        $addonSamples = [
            ['name' => 'Bulu Mata Premium', 'price' => 100000],
            ['name' => 'Touch Up 4 Jam', 'price' => 350000],
            ['name' => 'Hair Accessory Custom', 'price' => 250000],
            ['name' => 'Nail Art Simple', 'price' => 200000],
        ];

        foreach ($addonSamples as $item) {
            Addon::firstOrCreate(
                ['slug' => Str::slug($item['name'])],
                ['name' => $item['name'], 'price' => $item['price'], 'is_active' => true]
            );
        }

        $faqData = [
            ['question' => 'Berapa lama durasi makeup?', 'answer' => 'Durasi bervariasi 90-240 menit tergantung paket.'],
            ['question' => 'Apakah melayani home service?', 'answer' => 'Ya, tersedia home service dengan biaya tambahan.'],
            ['question' => 'Berapa minimal DP?', 'answer' => 'Minimal DP 50% untuk mengamankan jadwal booking.'],
        ];

        foreach ($faqData as $index => $item) {
            Faq::firstOrCreate(['question' => $item['question']], [
                'answer' => $item['answer'],
                'sort_order' => $index,
                'is_published' => true,
            ]);
        }

        Testimonial::firstOrCreate([
            'name' => 'Alya Putri',
            'message' => 'Hasil makeup flawless, tahan lama, dan sesuai request. Sangat recommended!',
        ], [
            'title' => 'Bride',
            'rating' => 5,
            'is_published' => true,
        ]);

        WebsiteSetting::firstOrCreate([], [
            'site_name' => 'Aurora Beauty MUA',
            'tagline' => 'Elegan, modern, dan feminin untuk momen terbaik Anda.',
            'contact_phone' => '081234567890',
            'contact_email' => 'hello@auroramua.com',
            'whatsapp_number' => '6281234567890',
            'address' => 'Jl. Kecantikan No. 10, Jakarta',
            'instagram_url' => 'https://instagram.com/auroramua',
            'meta_title' => 'Aurora Beauty MUA',
            'meta_description' => 'Layanan makeup wedding, wisuda, engagement, party, prewedding, editorial.',
            'theme_primary' => '#2563eb',
            'theme_secondary' => '#dbeafe',
        ]);

        foreach (range(1, 6) as $day) {
            AvailabilitySlot::firstOrCreate([
                'day_of_week' => $day,
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
            ], [
                'max_bookings' => 1,
                'is_active' => true,
            ]);

            AvailabilitySlot::firstOrCreate([
                'day_of_week' => $day,
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
            ], [
                'max_bookings' => 1,
                'is_active' => true,
            ]);
        }

        if (Portfolio::count() === 0) {
            $category = PortfolioCategory::where('slug', 'wedding')->first();
            Portfolio::create([
                'portfolio_category_id' => $category?->id,
                'title' => 'Soft Glam Wedding Look',
                'slug' => 'soft-glam-wedding-look',
                'summary' => 'Look bridal clean dan timeless dengan sentuhan glow natural.',
                'description' => 'Konsep soft glam untuk akad dan resepsi indoor dengan tone peach rose.',
                'is_published' => true,
                'work_date' => now()->subMonth(),
                'client_name' => 'Nabila',
            ]);
        }
    }
}
