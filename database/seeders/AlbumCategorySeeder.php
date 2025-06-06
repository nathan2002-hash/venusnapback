<?php

namespace Database\Seeders;

use App\Models\AlbumCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AlbumCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        $categories = [
            // ==================== PERSONAL ====================
            [
                'name' => 'Photography',
                'type' => 'personal',
                'description' => 'Photos, portraits, self-shots, personal captures'
            ],
            [
                'name' => 'Memories',
                'type' => 'personal',
                'description' => 'Family, friends, past moments, nostalgia'
            ],
            [
                'name' => 'Food',
                'type' => 'personal',
                'description' => 'Meals, drinks, daily eats'
            ],
            [
                'name' => 'Love & Life',
                'type' => 'personal',
                'description' => 'Relationship moments, lifestyle, personal updates'
            ],
            [
                'name' => 'Travel',
                'type' => 'personal',
                'description' => 'Places visited, vacation, nature walks'
            ],
            [
                'name' => 'Quotes',
                'type' => 'personal',
                'description' => 'Thoughts, feelings, inner voice, daily motivation'
            ],
            [
                'name' => 'Faith',
                'type' => 'personal',
                'description' => 'Christianity, prayer, worship, Bible reflections'
            ],
            [
                'name' => 'Personal Growth',
                'type' => 'personal',
                'description' => 'Journal, reflections, achievements, habits'
            ],
            [
                'name' => 'Fitness Journey',
                'type' => 'personal',
                'description' => 'Workout progress, body transformation, wellness'
            ],
            [
                'name' => 'Pets',
                'type' => 'personal',
                'description' => 'Pet photos, animal companions, cute moments'
            ],
            [
                'name' => 'Hobbies',
                'type' => 'personal',
                'description' => 'Crafts, gardening, collections, leisure activities'
            ],
            [
                'name' => 'Home Life',
                'type' => 'personal',
                'description' => 'House decor, cozy moments, family gatherings'
            ],
            [
                'name' => 'Books & Reading',
                'type' => 'personal',
                'description' => 'Favorite books, reading lists, literary quotes'
            ],
            [
                'name' => 'Music Tastes',
                'type' => 'personal',
                'description' => 'Favorite songs, playlists, concert experiences'
            ],
            [
                'name' => 'Gaming',
                'type' => 'personal',
                'description' => 'Video game screenshots, achievements, streams'
            ],

            // ==================== CREATOR ====================
            [
                'name' => 'Art & Design',
                'type' => 'creator',
                'description' => 'Drawings, digital art, illustrations, posters'
            ],
            [
                'name' => 'Fashion',
                'type' => 'creator',
                'description' => 'Outfits, styling, makeup, clothing design'
            ],
            [
                'name' => 'Photography',
                'type' => 'creator',
                'description' => 'Portraits, model shoots, edited visuals'
            ],
            [
                'name' => 'Meme',
                'type' => 'creator',
                'description' => 'Original jokes, relatable content, humor'
            ],
            [
                'name' => 'Music',
                'type' => 'creator',
                'description' => 'Song clips, lyrics, performances'
            ],
            [
                'name' => 'Dance',
                'type' => 'creator',
                'description' => 'Choreography, trends, steps'
            ],
            [
                'name' => 'Tutorials',
                'type' => 'creator',
                'description' => 'DIY, guides, how-tos'
            ],
            [
                'name' => 'Reviews',
                'type' => 'creator',
                'description' => 'Product reviews, demos, reactions'
            ],
            [
                'name' => 'Christianity',
                'type' => 'creator',
                'description' => 'Gospel content, teachings, inspirational posts'
            ],
            [
                'name' => 'Quotes',
                'type' => 'creator',
                'description' => 'Original quotes, motivational content'
            ],
            [
                'name' => 'Vlogging',
                'type' => 'creator',
                'description' => 'Behind-the-scenes, day-in-the-life content'
            ],
            [
                'name' => 'Comedy Skits',
                'type' => 'creator',
                'description' => 'Scripted humor, parodies, funny sketches'
            ],
            [
                'name' => 'Podcast Clips',
                'type' => 'creator',
                'description' => 'Audio highlights, discussions, interviews'
            ],
            [
                'name' => 'Writing',
                'type' => 'creator',
                'description' => 'Poetry, short stories, novel excerpts'
            ],
            [
                'name' => '3D Art',
                'type' => 'creator',
                'description' => 'Animations, CGI, digital sculptures'
            ],

            // ==================== BUSINESS ====================
            [
                'name' => 'Marketing',
                'type' => 'business',
                'description' => 'Campaigns, promotions, branding visuals'
            ],
            [
                'name' => 'Products',
                'type' => 'business',
                'description' => 'Product shots, listings, features'
            ],
            [
                'name' => 'Technology',
                'type' => 'business',
                'description' => 'App previews, gadgets, tech solutions'
            ],
            [
                'name' => 'Startup Life',
                'type' => 'business',
                'description' => 'Behind the scenes, team moments, office snaps'
            ],
            [
                'name' => 'Finance',
                'type' => 'business',
                'description' => 'Money tips, business advice, investment insights'
            ],
            [
                'name' => 'Services',
                'type' => 'business',
                'description' => 'What you offer, packages, client work'
            ],
            [
                'name' => 'Customer Reviews',
                'type' => 'business',
                'description' => 'Feedback, testimonials, user content'
            ],
            [
                'name' => 'Business Quotes',
                'type' => 'business',
                'description' => 'Entrepreneurial motivation, hustle mindset'
            ],
            [
                'name' => 'Christianity',
                'type' => 'business',
                'description' => 'Faith-based business inspiration'
            ],
            [
                'name' => 'Networking',
                'type' => 'business',
                'description' => 'Events, meetups, professional connections'
            ],
            [
                'name' => 'Workshops',
                'type' => 'business',
                'description' => 'Training sessions, webinars, educational content'
            ],
            [
                'name' => 'Case Studies',
                'type' => 'business',
                'description' => 'Success stories, project breakdowns, results'
            ],
            [
                'name' => 'Industry News',
                'type' => 'business',
                'description' => 'Trends, updates, market analysis'
            ],
            [
                'name' => 'Team Culture',
                'type' => 'business',
                'description' => 'Employee highlights, company values, workplace'
            ],
            [
                'name' => 'Sustainability',
                'type' => 'business',
                'description' => 'Eco-friendly practices, green initiatives'
            ],
        ];

        foreach ($categories as $category) {
            AlbumCategory::firstOrCreate(
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                    'user_id' => 1
                ],
                [
                    'description' => $category['description']
                ]
            );
        }
    }

}
