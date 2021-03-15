<?php namespace Octobro\Promo\ColumnTypes;

use Backend\Classes\ListColumn;
use Lang;
use Model;

class SortOrderField
{
    /**
     * Default field configuration
     * all these params can be overrided by column config
     * @var array
     */
    private static $defaultFieldConfig = [
        'request_down'    => 'onSetSortOrderDown',
        'request_up'      => 'onSetSortOrderUp'
    ];

    private static $listConfig = [];

    /**
     * constructor.
     *
     * @param            $value
     * @param ListColumn $column
     * @param Model      $record
     */
    public function __construct($value, ListColumn $column, Model $record)
    {
        $this->name   = $column->columnName;
        $this->value  = $value;
        $this->column = $column;
        $this->record = $record;
    }

    /**
     * @param       $field
     * @param array $config
     *
     * @internal param $name
     */
    public static function storeFieldConfig($field, array $config)
    {
        self::$listConfig[$field] = array_merge(self::$defaultFieldConfig, $config, ['name' => $field]);
    }


    /**
     * @param $config
     *
     * @return mixed
     */
    private function getConfig($config = null)
    {
        if (is_null($config)) {
            return self::$listConfig[$this->name];
        }

        return self::$listConfig[$this->name][$config];
    }

    private function getRequestData()
    {
        $modelClass = str_replace('\\', '\\\\', get_class($this->record));

        $data = [
            "id: {$this->record->{$this->record->getKeyName()}}",
            "field: '$this->name'",
            "model: '$modelClass'",
            "current_val: '$this->value'"
        ];

        if (post('page')) {
            $data[] = "page: " . post('page');
        }

        return implode(', ', $data);
    }

    public static function render($value, $column, $record)
    {
        $field        = new self($value, $column, $record);
        $config       = $field->getConfig();
        $request_data = $field->getRequestData();

        $value_preview = sprintf(' <span style="margin: 10px;">%s</span> ', $value);
        $button_up     = sprintf('<a href="javascript:;" class="btn btn-default btn-sm" data-request="%s" data-request-data="%s" data-stripe-load-indicator title="up"><i class="icon-arrow-up"></i></a>', $config['request_up'], $request_data);
        $button_down   = sprintf('<a href="javascript:;" class="btn btn-default btn-sm" data-request="%s" data-request-data="%s" data-stripe-load-indicator title="down"><i class="icon-arrow-down"></i></a>', $config['request_down'], $request_data);
        
        return $button_up. $value_preview .$button_down;
    }
    
}