<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $categories = [
            // Existing categories (only update description if they exist)
            ['user_id' => 1, 'name' => 'Photography', 'description' => 'Photos, portraits, landscapes, nature, camera, edits', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Fashion', 'description' => 'Clothes, outfits, style, makeup, beauty, modeling', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Food', 'description' => 'Meals, recipes, cooking, breakfast, lunch, dinner', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Travel', 'description' => 'Destinations, cities, mountains, beaches, adventures', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Fitness', 'description' => 'Gym, workout, health, body, training, muscle', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Art', 'description' => 'Drawings, painting, sketches, digital art, creativity', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Meme', 'description' => 'Funny, jokes, laughter, relatable, comedy', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Business', 'description' => 'Marketing, finance, startups, hustle, money', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Quotes', 'description' => 'Motivational, inspirational, life lessons, wisdom', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Technology', 'description' => 'Gadgets, apps, coding, AI, innovation, science', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Nature', 'description' => 'Animals, trees, sky, sunset, ocean, flowers', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Cars', 'description' => 'Vehicles, driving, speed, auto, engines', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Love', 'description' => 'Relationships, romance, dating, feelings, heart', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Christianity', 'description' => 'Bible, Jesus, prayer, gospel, faith, church', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Personal', 'description' => 'Life updates, thoughts, daily life, routine, journal', 'status' => 'active'],

            // New categories (added variety)
            ['user_id' => 2, 'name' => 'Gaming', 'description' => 'Video games, esports, consoles, PC gaming, gameplay', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Music', 'description' => 'Songs, artists, concerts, instruments, lyrics', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Movies', 'description' => 'Films, cinema, actors, reviews, trailers', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Books', 'description' => 'Novels, reading, authors, literature, reviews', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Sports', 'description' => 'Football, basketball, tennis, athletics, competitions', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'DIY', 'description' => 'Crafts, handmade, projects, home improvement', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Pets', 'description' => 'Dogs, cats, animals, care, training', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Parenting', 'description' => 'Kids, family, education, tips, child care', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Finance', 'description' => 'Investing, savings, budgeting, stocks, economy', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Health', 'description' => 'Wellness, medicine, fitness, mental health, tips', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Education', 'description' => 'Learning, schools, courses, students, teachers', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Politics', 'description' => 'Government, elections, policies, debates, news', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'History', 'description' => 'Past events, civilizations, wars, discoveries', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Science', 'description' => 'Research, discoveries, physics, biology, space', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Comedy', 'description' => 'Humor, stand-up, funny videos, satire', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Astrology', 'description' => 'Zodiac, horoscopes, tarot, spirituality', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Minimalism', 'description' => 'Simple living, decluttering, essentialism', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Cryptocurrency', 'description' => 'Bitcoin, blockchain, NFTs, trading, DeFi', 'status' => 'active'],
            ['user_id' => 2, 'name' => 'Futurism', 'description' => 'Tech trends, AI, robotics, future predictions', 'status' => 'active'],
        ];


   foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['name' => $category['name']], // Match by name
                $category // Update description if it exists
            );
        }
    }
}
