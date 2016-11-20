<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use \Mautic\CoreBundle\Templating\Helper\ButtonHelper;

if (!isset($item)) {
    $item = null;
}
$view['buttons']->reset($app->getRequest(), ButtonHelper::LOCATION_PAGE_ACTIONS, ButtonHelper::TYPE_BUTTON_DROPDOWN, $item);
include 'action_button_helper.php';

echo '<div class="std-toolbar btn-group">';

foreach ($templateButtons as $action => $enabled) {
    if (!$enabled) {
        continue;
    }

    if (!$enabled) {
        continue;
    }

    $path     = false;
    $primary  = false;
    $priority = 0;

    switch ($action) {
        case 'clone':
        case 'abtest':
            $actionQuery = [
                'objectId' => ('abtest' == $action && method_exists($item, 'getVariantParent') && $item->getVariantParent()) ? $item->getVariantParent()->getId() : $item->getId(),
            ];
            $icon = ($action == 'clone') ? 'copy' : 'sitemap';
            $path = $view['router']->path($actionRoute, array_merge(['objectAction' => $action], $actionQuery, $query));
            break;
        case 'close':
            $icon     = 'remove';
            $path     = $view['router']->path($indexRoute);
            $primary  = true;
            $priority = 200;
            break;
        case 'new':
        case'edit':
            $actionQuery = ('edit' == $action) ? ['objectId' => $item->getId()] : [];
            $icon        = ('edit' == $action) ? 'pencil-square-o' : 'plus';
            $path        = $view['router']->path($actionRoute, array_merge(['objectAction' => $action], $actionQuery, $query));
            $primary     = true;
            break;
        case 'delete':
            $view['buttons']->addButton(
                [
                    'confirm' => [
                        'message' => $view['translator']->trans(
                            'mautic.'.$langVar.'.form.confirmdelete',
                            ['%name%' => $item->$nameGetter().' ('.$item->getId().')']
                        ),
                        'confirmAction' => $view['router']->path(
                            $actionRoute,
                            array_merge(['objectAction' => 'delete', 'objectId' => $item->getId()], $query)
                        ),
                        'template' => 'delete',
                        'btnClass' => false,
                    ],
                    'priority' => -1,
                ]
            );
            break;
    }

    if ($path) {
        $view['buttons']->addButton(
            [
                'attr' => [
                    'class'       => 'btn btn-default',
                    'href'        => $path,
                    'data-toggle' => 'ajax',
                ],
                'iconClass' => 'fa fa-'.$icon,
                'btnText'   => $view['translator']->trans('mautic.core.form.'.$action),
                'priority'  => $priority,
                'primary'   => $primary,
            ]
        );
    }
}

if ($view['buttons']->getButtonCount() > 0) {
    // if any custom buttons are defined in the template $buttonCount=1 should display these in a dropdown,
    // a larger number will display them in a group
    // 0 will not display them
   // $view['buttons']->setGroupType(\Mautic\CoreBundle\Templating\Helper\ButtonHelper::TYPE_BUTTON_DROPDOWN);
    //$buttonCount = 1;
    echo '<div class="dropdown-toolbar btn-group">';

    $dropdownOpenHtml = '<button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-caret-down"></i></button>'
        ."\n";
    $dropdownOpenHtml .= '<ul class="dropdown-menu dropdown-menu-right" role="menu">'."\n";

    echo $view['buttons']->renderButtons($dropdownOpenHtml);

    echo '</ul></div>';
}

echo '</div>';
echo $extraHtml;
