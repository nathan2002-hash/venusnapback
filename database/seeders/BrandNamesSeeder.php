<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            // Technology
            'Microsoft', 'Apple', 'Google', 'Amazon', 'Facebook', 'Twitter', 'Instagram',
            'Samsung', 'Sony', 'LG', 'Intel', 'AMD', 'NVIDIA', 'IBM', 'Oracle', 'Cisco',
            'HP', 'Dell', 'Lenovo', 'Asus', 'Acer', 'Huawei', 'Xiaomi', 'OnePlus',
            'Netflix', 'Spotify', 'Adobe', 'Salesforce', 'Slack', 'Zoom', 'TikTok',
            'Snapchat', 'Pinterest', 'Reddit', 'LinkedIn', 'YouTube', 'WhatsApp', 'Telegram',
            'Viber', 'Signal', 'Discord', 'GitHub', 'Bitbucket', 'Figma', 'Canva',
            'Dropbox', 'Box', 'GoogleDrive', 'iCloud', 'OneDrive', 'WeTransfer',
            'Zoom', 'Skype', 'Teams', 'Slack', 'Trello', 'Asana', 'Jira', 'Confluence',
            'WordPress', 'Wix', 'Squarespace', 'Shopify', 'Magento', 'WooCommerce',
            'BigCommerce', 'PrestaShop', 'OpenCart', 'Drupal', 'Joomla', 'Blogger',
            'Tumblr', 'Medium', 'Substack', 'Ghost', 'Typepad', 'Weebly', 'Webflow',
            'Mailchimp', 'ConstantContact', 'SendGrid', 'AWeber', 'GetResponse',
            'Twilio', 'OpenAI', 'DeepSeek', 'Pardot', 'Infusionsoft', 'ConvertKit',
            'ActiveCampaign', 'HubSpot', 'Marketo', 'Zoho', 'Salesforce', 'Pipedrive',
            'Freshworks', 'Zendesk', 'Intercom', 'Drift', 'Olark', 'LiveChat',
            'HelpScout', 'Kayako', 'Freshdesk', 'ZohoDesk', 'Groove', 'Tawk.to',
            'DigitalOcean', 'AWS', 'Azure', 'GoogleCloud', 'IBMCloud', 'AlibabaCloud',
            'Heroku', 'Firebase', 'Linode', 'Vultr', 'Netlify', 'Cloudflare', 'Fastly',
            'GitLab', 'Bitbucket', 'SourceForge', 'CodePen', 'JSFiddle', 'Replit',
            'Atlassian', 'Laravel', 'Symfony', 'Django', 'Flask', 'RubyOnRails',


            // Automotive
            'Toyota', 'Honda', 'Ford', 'Chevrolet', 'BMW', 'Mercedes', 'Audi', 'Volkswagen',
            'Tesla', 'Nissan', 'Hyundai', 'Kia', 'Porsche', 'Ferrari', 'Lamborghini',
            'Volvo', 'Subaru', 'Mazda', 'Jeep', 'Chrysler', 'LandRover', 'Jaguar',

            // Food & Beverage
            'CocaCola', 'Pepsi', 'McDonalds', 'KFC', 'BurgerKing', 'Starbucks', 'Nestle',
            'Kelloggs', 'Heinz', 'Danone', 'Kraft', 'Unilever', 'Pringles', 'Lays',
            'RedBull', 'Monster', 'Sprite', 'Fanta', 'DrPepper', 'Tropicana',

            // Fashion
            'Nike', 'Adidas', 'Puma', 'Gucci', 'LouisVuitton', 'Chanel', 'Hermes',
            'Zara', 'H&M', 'Uniqlo', 'Levis', 'UnderArmour', 'CalvinKlein', 'TommyHilfiger',
            'Rolex', 'Omega', 'Cartier', 'Tiffany', 'RayBan', 'Oakley',

            // Add 400+ more brands across various categories...
            // Financial
            'Visa', 'Mastercard', 'AmericanExpress', 'PayPal', 'JPMorgan', 'BankofAmerica',
            'WellsFargo', 'GoldmanSachs', 'MorganStanley', 'Citigroup', 'Barclays', 'HSBC',

            // Telecommunications
            'Verizon', 'AT&T', 'T-Mobile', 'Sprint', 'Vodafone', 'Orange', 'DeutscheTelekom',
            'BTGroup', 'Telefonica', 'TelecomItalia', 'ChinaMobile', 'ChinaUnicom', 'ChinaTelecom',

            // Pharmaceuticals
            'Pfizer', 'Johnson&Johnson', 'Merck', 'Novartis', 'Roche', 'AstraZeneca', 'GlaxoSmithKline',
            'Sanofi', 'Bayer', 'AbbVie', 'Gilead', 'Amgen', 'BristolMyersSquibb', 'EliLilly',

            // Airlines
            'Delta', 'United', 'AmericanAirlines', 'Lufthansa', 'Emirates', 'SingaporeAirlines',

            // Retail
            'Walmart', 'Target', 'Costco', 'BestBuy', 'IKEA', 'HomeDepot', 'Lowe',
        ];

          foreach ($brands as $brand) {
            DB::table('reserved_names')->insertOrIgnore([
                'name' => $brand,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
