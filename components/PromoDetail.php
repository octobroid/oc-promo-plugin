<?php namespace Octobro\Promo\Components;

use Cms\Classes\ComponentBase;
use Octobro\Promo\Models\Promo;

class PromoDetail extends ComponentBase
{
    public $promo;

    public function componentDetails()
    {
        return [
            'name'        => 'promoDetail Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Slug',
                'description' => '',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
        ];
    }

    public function onRun()
    {
        $this->promo = $this->page['promo'] = $this->loadPromo();

        $this->page->title = $this->promo->name;
    }

    protected function loadPromo()
    {
        $slug = $this->property('slug');

        return Promo::whereSlug($slug)->first();
    }

}