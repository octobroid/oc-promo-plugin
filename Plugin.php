<?php namespace Octobro\Promo;

use Backend\Classes\Controller;
use Event, Flash;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;

class Plugin extends PluginBase
{
    public $require = ['RainLab.User'];

    public function boot()
    {
        $this->extendBackendListColumns();
        $this->extendBackendController();
    }

    public function registerComponents()
    {
    	return [
    		'Octobro\Promo\Components\CouponValidator' => 'couponValidator',
            'Octobro\Promo\Components\PromoDetail'     => 'promoDetail',
    	];
    }

    public function register()
    {
        $alias = AliasLoader::getInstance();
        $alias->alias('Promo', 'Octobro\Promo\Facades\Promo');
    }

    public function registerListColumnTypes()
    {
        return [
            'oc-sort-order' => [\Octobro\Promo\ColumnTypes\SortOrderField::class, 'render'],
        ];
    }

    protected function extendBackendListColumns()
    {
        Event::listen('backend.list.extendColumns', function ($widget) {
            /** @var \Backend\Widgets\Lists $widget */
            foreach ($widget->config->columns as $name => $config) {
                if (empty($config['type']) || $config['type'] !== 'oc-sort-order') {
                    continue;
                }

                \Octobro\Promo\ColumnTypes\SortOrderField::storeFieldConfig($name, $config);

                $widget->addColumns([
                    $name => array_merge($config, [
                        'clickable' => false,
                    ]),
                ]);
            }
        });
    }

    protected function extendBackendController()
    {
        /**
         * Switch a boolean value of a model field
         * @return void
         */
        Controller::extend(function ($controller) {
            /** @var Controller $controller */
            $controller->addDynamicMethod('index_onSetSortOrderUp', function () use ($controller) {
                $field       = post('field');
                $id          = post('id');
                $model_class = post('model');
                $current_val = post('current_val');

                if (empty($field) || empty($id) || empty($model_class)) {
                    Flash::error('Following parameters are required : id, field, model');
                    return;
                }

                $model = new $model_class;
                $item = $model::find($id);
                $item->{$field} = $current_val + 1;

                $item->save();

                Flash::success(sprintf('%s Changed Sort Order', ucwords($item->name)));

                return $controller->listRefresh($controller->primaryDefinition);
            });

            $controller->addDynamicMethod('index_onSetSortOrderDown', function () use ($controller) {
                $field       = post('field');
                $id          = post('id');
                $model_class = post('model');
                $current_val = post('current_val');

                if (empty($field) || empty($id) || empty($model_class)) {
                    Flash::error('Following parameters are required : id, field, model');
                    return;
                }

                $model = new $model_class;
                $item = $model::find($id);
                $item->{$field} = $current_val - 1;

                $item->save();

                Flash::success(sprintf('%s Changed Sort Order', ucwords($item->name)));

                return $controller->listRefresh($controller->primaryDefinition);
            });
        });
    }

}
