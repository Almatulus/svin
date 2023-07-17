<?php
namespace common\components;

use core\models\company\CompanyPosition;
use core\models\division\Division;
use core\models\division\DivisionPayment;
use core\models\order\Order;
use core\models\Staff;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Class Menu
 * Theme menu widget.
 */
class Menu extends \yii\widgets\Menu
{
    public $renderForm = false;
    public $formView = null;

    public $view = 'list';
    // show submenu or not
    public $showSubmenu = true;
    // index of submenu
    private $submenuIndex = -1;

    /**
     * Renders the menu.
     */
    public function run()
    {
        if ($this->route === null && Yii::$app->controller !== null) {
            $this->route = Yii::$app->controller->getRoute();
        }
        if ($this->params === null) {
            $this->params = Yii::$app->request->getQueryParams();
        }
        $items = $this->normalizeItems($this->items, $hasActiveChild);
        if (!empty($items)) {
            $options = $this->options;
            $tag     = ArrayHelper::remove($options, 'tag', 'ul');

            echo '<div class="mainnav" id="mainnav">';
            echo $this->render('//layout/sidenav/_header');

            if ($tag !== false) {
                echo Html::tag($tag, $this->renderItems($items), $options);
            } else {
                echo $this->renderItems($items);
            }
            echo '</div>';

            if ($this->submenuIndex >= 0 && $this->showSubmenu == true) {
                echo $this->renderSidenav($items);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public $linkTemplate = '<a href="{url}">{icon} {label}</a>';
    public $submenuTemplate = "\n<ul class='treeview-menu' {show}>\n{items}\n</ul>\n";
    public $activateParents = true;

    /**
     * @inheritdoc
     */
    protected function renderItem($item)
    {
        $linkTemplate = $this->linkTemplate;
        if (isset($item['url'])) {
            $template = ArrayHelper::getValue($item, 'template', $linkTemplate);
            $replace  = !empty($item['icon']) ? [
                '{url}' => Url::to($item['url']),
                '{label}' => '<span>' . $item['label'] . '</span>',
                '{icon}' => '<i class="' . $item['icon'] . '"></i> '
            ] : [
                '{url}' => Url::to($item['url']),
                '{label}' => '<span>' . $item['label'] . '</span>',
                '{icon}' => null,
            ];
            return strtr($template, $replace);
        } else {
            $template = ArrayHelper::getValue($item, 'template', $this->labelTemplate);
            $replace  = !empty($item['icon']) ? [
                '{label}' => '<span>' . $item['label'] . '</span>',
                '{icon}' => '<i class="' . $item['icon'] . '"></i> '
            ] : [
                '{label}' => '<span>' . $item['label'] . '</span>',
            ];
            return strtr($template, $replace);
        }
    }

    /**
     * Recursively renders the menu items (without the container tag).
     * @param array $items the menu items to be rendered recursively
     * @return string the rendering result
     */
    protected function renderItems($items)
    {
        $n     = count($items);
        $lines = [];
        foreach ($items as $i => $item) {
            $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
            $tag     = ArrayHelper::remove($options, 'tag', 'li');
            $class   = [];
            if ($item['active']) {
                $class[] = $this->activeCssClass;
            }
            if ($i === 0 && $this->firstItemCssClass !== null) {
                $class[] = $this->firstItemCssClass;
            }
            if ($i === $n - 1 && $this->lastItemCssClass !== null) {
                $class[] = $this->lastItemCssClass;
            }
            if (!empty($class)) {
                if (empty($options['class'])) {
                    $options['class'] = implode(' ', $class);
                } else {
                    $options['class'] .= ' ' . implode(' ', $class);
                }
            }
            $menu = $this->renderItem($item);
//            if (!empty($item['items'])) {
//                $menu .= strtr($this->submenuTemplate, [
//                    '{show}' => $item['active'] ? "style='display: block'" : '',
//                    '{items}' => $this->renderItems($item['items']),
//                ]);
//            }
            $lines[] = Html::tag($tag, $menu, $options);
        }
        return implode("\n", $lines);
    }

    /**
     * @inheritdoc
     */
    protected function normalizeItems($items, &$active)
    {
        foreach ($items as $i => $item) {
            if (isset($item['visible']) && !$item['visible']) {
                unset($items[$i]);
                continue;
            }
            if (!isset($item['label'])) {
                $item['label'] = '';
            }
            $encodeLabel        = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            $items[$i]['label'] = $encodeLabel ? Html::encode($item['label']) : $item['label'];
            $items[$i]['icon']  = isset($item['icon']) ? $item['icon'] : '';
            $hasActiveChild     = false;
            if (isset($item['items'])) {
                $items[$i]['items']          = $this->normalizeItems($item['items'], $hasActiveChild);
                $items[$i]['hasActiveChild'] = $hasActiveChild;
                if (empty($items[$i]['items']) && $this->hideEmptyItems) {
                    unset($items[$i]['items']);
                    if (!isset($item['url'])) {
                        unset($items[$i]);
                        continue;
                    }
                }
            }
            if (!isset($item['active'])) {
                if ($this->activateParents && $hasActiveChild || $this->activateItems && $this->isItemActive($item)) {
                    $this->submenuIndex = $i;
                    $active             = $items[$i]['active'] = true;
                } else {
                    $items[$i]['active'] = false;
                }
            } elseif ($item['active']) {
                $active = true;
            }
        }
        return array_values($items);
    }

    /**
     * Checks whether a menu item is active.
     * This is done by checking if [[route]] and [[params]] match that specified in the `url` option of the menu item.
     * When the `url` option of a menu item is specified in terms of an array, its first element is treated
     * as the route for the item and the rest of the elements are the associated parameters.
     * Only when its route and parameters match [[route]] and [[params]], respectively, will a menu item
     * be considered active.
     * @param array $item the menu item to be checked
     * @return boolean whether the menu item is active
     */
    protected function isItemActive($item)
    {
        if (isset($item['url']) && is_array($item['url']) && isset($item['url'][0])) {
            $route = $item['url'][0];
            if ($route[0] !== '/' && Yii::$app->controller) {
                $route = Yii::$app->controller->module->getUniqueId() . '/' . $route;
            }
            $arrayRoute     = explode('/', ltrim($route, '/'));
            $arrayThisRoute = explode('/', $this->route);
            if ($arrayRoute[0] !== $arrayThisRoute[0]) {
                return false;
            }
            if (isset($arrayRoute[1]) && $arrayRoute[1] !== $arrayThisRoute[1]) {
                return false;
            }
            if (isset($arrayRoute[2]) && $arrayRoute[2] !== $arrayThisRoute[2]) {
                return false;
            }
            unset($item['url']['#']);
            if (count($item['url']) > 1) {
                foreach (array_splice($item['url'], 1) as $name => $value) {
                    if ($value !== null && (!isset($this->params[$name]) || $this->params[$name] != $value)) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    private function renderSidenav($items)
    {
        $submenu = '<div class="sidenav" id="sidenav">';
        if ($items[$this->submenuIndex]['id'] == 'timetable' || $this->view == 'datepicker') {
            $submenu .= $this->renderTimetableMenu();
        } else if (($items[$this->submenuIndex]['id'] == 'services'
            || $items[$this->submenuIndex]['id'] == 'warehouse')
            && $this->view == 'tree') {
            $submenu .= $this->renderServicesMenu($items);
        } else {
            $submenu .= $this->renderDefaultMenu($items);
        }
        if ($this->renderForm == true) {
            $submenu .= $this->formView;
        }
        $submenu .= '</div>';

        return $submenu;
    }

    /**
     * @return string
     */
    private function renderTimetableMenu()
    {
        $query = Staff::find()->company()->permitted()->valid()->orderBy('id');
        if (isset(Yii::$app->user->identity->staff)
            && Yii::$app->user->identity->staff->see_own_orders
        ) {
            $query->andWhere([
                '{{%staffs}}.id' => Yii::$app->user->identity->staff->id
            ]);
        }
        $staffs = $query->all();

        /* @var Division[] $models */
        $models = Division::find()->company()->permitted()->enabled()->all();
        $divisionsList = ArrayHelper::map($models, 'id', 'name');
        $selected_division_id = key($divisionsList);

        $divisionOptions = [];
        foreach ($models as $model) {
            $payments = array_map(function (DivisionPayment $divisionPayment) {
                return [
                    'id'     => $divisionPayment->payment_id,
                    'name'   => Yii::t('app', $divisionPayment->payment->name),
                    'amount' => 0,
                    'type'   => $divisionPayment->payment->type,
                ];
            }, $model->getDivisionPayments()->joinWith('payment')->all());

            $divisionOptions[$model->id] = [
                'data-address'   => $model->address,
                'data-can-print' => intval($model->canPrintOrder()),
                'data-payments'  => Json::encode($payments),
                'data-logo'      => $model->getLogoPath()
            ];
        }

        $positions = CompanyPosition::find()
                                    ->notDeleted()
                                    ->company(Yii::$app->user->identity->company_id)
                                    ->all();
        $positionsList = ArrayHelper::map($positions, 'id', 'name');

        $waitingList = Order::find()
                ->waiting()
                ->company()
                ->orderBy('datetime DESC')
                ->with(['companyCustomer.customer', 'staff'])
                ->all();

        return $this->getView()->render(
            "\sidenav\_timetable",
            compact(
                'staffs',
                'divisionsList',
                'selected_division_id',
                'divisionOptions',
                'waitingList',
                'positionsList'
            )
        );
    }

    private function renderServicesMenu($items)
    {
        $path = self::getCategoryController();

        $submenu = Html::beginTag('div', ['class' => "column_row"]);

        if (Yii::$app->user->can('serviceCategoryCreate')) {
            $submenu .= Html::a('Добавить категорию', 'javascript:;', [
                'class'    => 'btn btn-primary btn-add-category',
                'style'    => 'display: none; margin: 10px 0 20px;',
                'data-url' => $path . '/new'
            ]);
        }

        $submenu .= Html::beginTag('div', ['class' => "tree"]);
        foreach ($items[$this->submenuIndex]['items'] as $i => $item) {
            $icon = !empty($item['icon']) ? Html::tag('i', '', ['class' => $item['icon']]) : '';
            if ($i == 0) {
                $content = Html::tag('div',  $icon . ' ' . $item['label'], ['class' => 'icon_box']);
                $anchor = Html::a($content, Url::to($item['url']), [
                    'class' => 'item root ' . (($item['active'] && $items[$this->submenuIndex]['id'] != 'customers') ? 'active' : ''),
                    'title' => $item['label'],
                ]);
                $submenu .= $anchor;
                $submenu .= Html::beginTag('ul');
            } else {
                $submenu .= Html::beginTag('li');
                $content = Html::tag('span', $item['label']);
                $anchor = Html::a($content, Url::to($item['url']), [
                    'class' => $item['active'] ? 'active' : '',
                    'title' => $item['label'],
                ]);
                $submenu .= $anchor;
                if (isset($item['items'])) {
                    if ($item['active'] == true || (isset($item['hasActiveChild']) && $item['hasActiveChild'] == true)) {
                        $submenu .= $this->getView()->render('\sidenav\_submneu', [
                            'items' => $item['items'],
                        ]);
                    }
                }
                $submenu .= Html::endTag('li');
            }
        }
        $submenu .= Html::endTag('ul');
        $submenu .= Html::endTag('div');

        if ($this->view == 'tree' && (Yii::$app->user->can('serviceCategoryCreate')
                || Yii::$app->user->can('serviceCategoryUpdate')
                || Yii::$app->user->can('serviceCategoryDelete')
            )) {
            $submenu .= Html::beginTag('div', ['class' => 'tree_options']);
            $submenu .= Html::a('добавить/изменить/удалить', "javascript:;", [
                'id' => "btn-edit-categories",
                'data-url' => $path . '/tree'
            ]);
            $submenu .= Html::endTag('div');
        }

        $submenu .= Html::a('Готово', "#", [
                'class' => "btn btn-done",
                'onclick' => 'location.reload(); return false;',
                'style' => 'display: none; margin-top: 20px;'
            ]);
        $submenu .= Html::endTag('div');
        return $submenu;
    }

    private function renderDefaultMenu($items)
    {
        return $this->getView()->render('\sidenav\_default', [
            'id' => $items[$this->submenuIndex]['id'],
            'items' => $items[$this->submenuIndex]['items'],
            'title' => $items[$this->submenuIndex]['label'],
            'view' => $this->view
        ]);
    }

    private function getCategoryController() {
        $path = null;
        switch($this->items[$this->submenuIndex]['id']) {
            case 'services':
                $path = '/service-category';
                break;
            case 'warehouse':
                $path = '/warehouse/category';
                break;
        }
        return $path;
    }

}