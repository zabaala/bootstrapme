<?php

namespace Zabaala\Bootstrapme;

use Illuminate\Html\HtmlBuilder as IlluminateHtmlBuilder;
use Illuminate\Support\Facades\Request;

class HtmlBuilder extends IlluminateHtmlBuilder{

    public function breadcrumbs(array $configs) {

        $method = explode('.', Request::route()->getName());

        $method = end($method);

        $html  = '<div class="breadcrumbs">';
        $html .= '<div class="container">';
        $html .= '<ol class="breadcrumb">';
        $html .= '<li>Você está aqui:</li>';
        $html .= '<li>Dashboard</li>';
        $html .= '<li>' . $configs['breadcrumbs']['base'] . '</li>';

        if(array_key_exists('name', $configs)) {
            $html .= '<li>' . $configs['name']['plural'] . '</li>';
        }

        if(array_key_exists($method, $configs['breadcrumbs'])) {
            $html .= '<li>' . $configs['breadcrumbs'][$method] . '</li>';
        }

        $html .= '</ol>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a HTML link.
     *
     * @param  string  $url
     * @param  string  $title
     * @param  array   $attributes
     * @param  bool    $secure
     * @return string
     */
    public function link($url, $title = null, $attributes = array(), $secure = null)
    {
        $url = $this->url->to($url, array(), $secure);

        if (is_null($title) || $title === false) $title = $url;

        return '<a href="'.$url.'"'.$this->attributes($attributes).'>'.$title.'</a>';
    }


    /**
     * Generate html sortable link.
     *
     * @param $name
     * @param $label
     * @param string $upClassName
     * @param string $downClassName
     * @return string
     */
    public function sortable($name, $label, $upClassName = 'fa-chevron-up', $downClassName = 'fa-chevron-down'){

        $requestName        = Request::get('orderBy');
        $requestOrientation = Request::get('sortedBy');
        $className          = '';

        if (! $requestOrientation) {
            $nextOrientation = 'asc';
            $className = $upClassName;
        }
        elseif ($requestOrientation=='asc') {
            $nextOrientation = 'desc';
            $className = $upClassName;
        }
        elseif ($requestOrientation=='desc') {
            $nextOrientation = 'asc';
            $className = $downClassName;
        }

        if($requestName!=$name){
            $className = '';
        }

        $html  = '<i class="fa '.$className.'"></i> ';
        $html .= '<a href="?orderBy='.$name.'&sortedBy='.$nextOrientation.'">' . $label . '</a>';

        return $html;
    }

    /**
     * Generate Backend action bar.
     * @param $title
     * @param $buttons
     * @return string
     */
    public function actionBar($title, $buttons) {

        $html  = '<div class="row">';

        $html .= '<div class="col-lg-6"><h1 class="lead title">' .$title. '</h1></div>';

            $html .= '<div class="col-lg-6 action-buttons text-right">';

            foreach ($buttons as $button) {
                $html .= $this->link($button['url'], $button['title'], ['class' => $button['class']]) . ' ';
            }

            $html .= '</div>';

        $html .= '</div>';

        return $html;

    }

}