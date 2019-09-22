<?php namespace Octobro\Promo\Controllers;

use Flash;
use Backend;
use Exception;
use Backend\Classes\Controller;
use Octobro\Promo\Models\Coupon;
use BackendMenu;

class Promos extends Controller
{
    public $implement = [
    	'Backend\Behaviors\ListController',
    	'Backend\Behaviors\FormController',
    	'Backend\Behaviors\RelationController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Octobro.Promo', 'promo', 'promos');
    }

    public function update_onShowCouponGeneratorForm($recordId)
    {
        try {
            $promo = $this->formFindModelObject($recordId);
            // $this->vars['currentStatus'] = isset($invoice->status->name) ? $invoice->status->name : '???';
            $this->vars['widget'] = $this->makeCouponGeneratorFormWidget($promo);
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }

        return $this->makePartial('coupon_generator_form');
    }

    public function update_onGenerateCoupons($recordId = null)
    {
        // dd(post());
        Coupon::generate($recordId, post('quantity'), post('length'), post('prefix'), post('stock'));
        
        Flash::success('promo status updated.');

        return Backend::redirect(sprintf('octobro/promo/promos/update/%s', $recordId));
    }

    protected function makeCouponGeneratorFormWidget($promo)
    {
        $model = new Coupon;
        $model->promo = $promo;

        $config = $this->makeConfig('~/plugins/octobro/promo/controllers/promos/coupon_generator_fields.yaml');
        $config->model = $model;

        return $this->makeWidget('Backend\Widgets\Form', $config);
    }
}