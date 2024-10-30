<?php

namespace Database\Factories;

use App\Models\Model;
use App\AramiscAboutPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class AramiscAboutPageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AramiscAboutPage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'About Aramisc',
            'description' => 'Lisus consequat sapien metus dis urna, facilisi. Nonummy rutrum eu lacinia platea a, ipsum parturient, orci tristique. Nisi diam natoque.',
            'image' => 'public/uploads/about_page/about.jpg',
            'button_text' => 'Learn More About Us',
            'button_url' => 'about',
            'main_title'=>'Under Graduate Education',
            'main_description'=>'Aramisc has all in one place. You’ll find everything what you are looking into education management system software. We care! User will never bothered in our real eye catchy user friendly UI & UX  Interface design. You know! Smart Idea always comes to well planners. And Our Aramisc is Smart for its Well Documentation. Explore in new support world! It’s now faster & quicker. You’ll find us on Support Ticket, Email, Skype, WhatsApp.',
            'main_image'=>'public/uploads/about_page/about-img.jpg',
        ];
    }
}
