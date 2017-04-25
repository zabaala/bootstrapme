<?php

namespace Zabaala\Bootstrapme;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Html\FormBuilder as IlluminateFormBuilder;

class FormBuilder extends IlluminateFormBuilder
{
    /**
     * An array containing the currently opened form groups.
     *
     * @var array
     */
    protected $groupStack = array();

    /**
     * Create a new form builder instance.
     *
     * @param HtmlBuilder $html
     * @param UrlGenerator|\Illuminate\Routing\UrlGenerator $url
     * @param  string $csrfToken
     * @return FormBuilder
     */
    public function __construct(HtmlBuilder $html, UrlGenerator $url, $csrfToken)
    {
        parent::__construct($html, $url, $csrfToken);

        $this->url = $url;
        $this->html = $html;
        $this->csrfToken = $csrfToken;
    }

    /**
     * Append the given key/value pair to the given options array.
     *
     * @param $key
     * @param $value
     * @param  array $options
     * @internal param string $class
     * @return array
     */
    private function appendToOptions($key, $value, array $options = array())
    {
        // If a 'class' is already specified, append the 'form-control'
        // class to it. Otherwise, set the 'class' to 'form-control'.
        $options[$key] = isset($options[$key]) ? $options[$key].' ' : '';
        $options[$key] .= $value;

        return $options;
    }

    /**
     * Determine whether the form element with the given name
     * has any validation errors.
     *
     * @param  string  $name
     * @return bool
     */
    private function hasErrors($name)
    {
        if (is_null($this->session) || ! $this->session->has('errors'))
        {
            // If the session is not set, or the session doesn't contain
            // any errors, the form element does not have any errors
            // applied to it.
            return false;
        }

        // Get the errors from the session.
        $errors = $this->session->get('errors');

        // Check if the errors contain the form element with the given name.
        // This leverages Laravel's transformKey method to handle the
        // formatting of the form element's name.
        return $errors->has($this->transformKey($name));
    }

    /**
     * Get the formatted errors for the form element with the given name.
     *
     * @param  string  $name
     * @return string
     */
    private function getFormattedErrors($name)
    {
        if ( ! $this->hasErrors($name))
        {
            // If the form element does not have any errors, return
            // an emptry string.
            return '';
        }

        // Get the errors from the session.
        $errors = $this->session->get('errors');

        // Return the formatted error message, if the form element has any.
        return $errors->first($this->transformKey($name), '<p class="help-block">:message</p>');
    }

    /**
     * Open a new form group.
     *
     * @param  string  $name
     * @param  mixed   $label
     * @param  array   $options
     * @return string
     */
    protected function openGroup($name, $label = null, $options = array())
    {
        $options = $this->appendToOptions('class', 'form-group', $options);

        // Append the name of the group to the groupStack.
        $this->groupStack[] = $name;

        if ($this->hasErrors($name))
        {
            // If the form element with the given name has any errors,
            // apply the 'has-error' class to the group.
            $options = $this->appendToOptions('class', 'has-error', $options);
        }

        $label = $this->label($name, $label);

        return '<div'.$this->html->attributes($options).'>'.html_entity_decode($label);
    }

    /**
     * Create a form label element.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     * @return string
     * @throws BootstrapmeException
     */
    public function label($name, $value = null, $options = array())
    {
        /**
         * @todo // If a label is given, we set it up here. Otherwise, we will just set it to an empty string.
         */

        $infoHtml = '';
        $label = $value;

        if (is_array($value)) {

            if(! key_exists('name', $value)) {
                throw new BootstrapmeException('Key name not found in array $value');
            }

            $label = $value['name'];

            if (key_exists('info', $value)) {
                if (! (key_exists('title', $value['info']) && key_exists('description', $value['info'])) ) {
                    throw new BootstrapmeException('Title and description keys should be passed to key: info');
                }

                $infoPosition = key_exists('position', $value['info']) ? $value['info']['position'] : 'top';
                $infoIcoClass = key_exists('class', $value['info']) ? $value['info']['class'] : 'fa fa-info-circle';

                $infoHtml = '<span 
                                class="popovers" 
                                data-trigger="hover" 
                                data-placement="' . $infoPosition . '" 
                                data-content="' . $value['info']['description'] . '" 
                                data-original-title="' . $value['info']['title'] . '" 
                                data-html="true">
                                <i id="popover-box-helper-icon-plan_setup_tax" class="' . $infoIcoClass . '"></i>
                             </span>';
            }
        }
        
        $this->labels[] = $label;

        $options = $this->html->attributes($options);

        $value = e($this->formatLabel($name, $label));

        return '<label for="'.$name.'"'.$options.'>'.$value . ': ' . $infoHtml .'</label>';
    }

    /**
     * Close out the last opened form group.
     *
     * @return string
     */
    protected function closeGroup()
    {
        // Get the last added name from the groupStack and
        // remove it from the array.
        $name = array_pop($this->groupStack);

        // Get the formatted errors for this form group.
        $errors = $this->getFormattedErrors($name);

        // Append the errors to the group and close it out.
        return $errors.'</div>';
    }

    /**
     * Make a bootstrap form-group.
     *
     * @param $type
     * @param $name
     * @param null $label
     * @param null $value
     * @param array $options
     * @return string
     */
    protected function maker($type, $name, $label = null, $value = null, $options = []) {
        $options = $this->appendToOptions('id', $name, $options);
        $options = $this->appendToOptions('class', 'form-control', $options);

        $html  = $this->openGroup($name, $label);
        $html .= parent::input($type, $name, $value, $options);
        $html .= $this->closeGroup();

        return $html;
    }

    /**
     * Generate bootstrap grouped HTML Form type text.
     * @see http://getbootstrap.com/css/#forms
     *
     * @param $name
     * @param $label
     * @param $value
     * @param array $options
     * @return string
     */
    public function text($name, $label = null, $value = null, $options = [])
    {
        return $this->maker('text', $name, $label, $value, $options);
    }

    /**
     * Generate bootstrap grouped HTML Form type text.
     * @see http://getbootstrap.com/css/#forms
     *
     * @param $name
     * @param $label
     * @param $value
     * @param array $options
     * @return string
     */
    public function number($name, $label = null, $value = null, $options = [])
    {
        return $this->maker('number', $name, $label, $value, $options);
    }

    /**
     * Generate bootstrap grouped HTML Form type textarea.
     * @see http://getbootstrap.com/css/#forms
     *
     * @param $name
     * @param $label
     * @param $value
     * @param array $options
     * @return string
     */
    public function textarea($name, $label = null, $value = null, $options = [])
    {
        return $this->maker('textarea', $name, $label, $value, $options);
    }

    /**
     * Generate bootstrap grouped HTML Form type text.
     * @see http://getbootstrap.com/css/#forms
     *
     * @param $name
     * @param $label
     * @param $value
     * @param array $options
     * @return string
     */
    public function color($name, $label = null, $value = null, $options = [])
    {
        return $this->maker('color', $name, $label, $value, $options);
    }

    /**
     * Generate bootstrap grouped HTML Form type email.
     * @see http://getbootstrap.com/css/#forms
     *
     * @param $name
     * @param $label
     * @param $value
     * @param array $options
     * @return string
     */
    public function email($name, $label = null, $value = null, $options = [])
    {
        return $this->maker('email', $name, $label, $value, $options);
    }

    /**
     * Generate bootstrap grouped HTML Form type password.
     * @see http://getbootstrap.com/css/#forms
     *
     * @param string $name
     * @param null $label
     * @param array $options
     * @return string
     */
    public function password($name, $label = null, $options = [])
    {
        return $this->maker('password', $name, $label, '', $options);
    }

    /**
     * Generate bootstrap grouped HTML Form type checkbox.
     * @see http://getbootstrap.com/css/#forms
     *
     * @param string $name
     * @param null $label
     * @param int $value
     * @param null $checked
     * @param array $options
     * @return string
     */
    public function checkbox($name, $label = null, $value = 1, $checked = null, $options = []) {
        $options = $this->appendToOptions('id', $name, $options);
//        $options = $this->appendToOptions('class', 'form-control', $options);

        $html  = $this->openGroup($name, $label);
        $html .= parent::checkbox($name, $value, $checked, $options);
        $html .= $this->closeGroup();

        return $html;
    }

    /**
     * Generate a HTML select grouped by bootstrap group container.
     * The value passed to $list can be a array with contains a pair of key/value or
     * a \Illuminate\Database\Eloquent\Collection contains key and value fields.
     *
     * @param string $name
     * @param null $label
     * @param array $list
     * @param null $selected
     * @param array $options
     * @return string
     */
    public function select($name, $label = null, $list = [], $selected = null, $options = [])
    {
        if($list instanceof \Illuminate\Support\Collection) {

            $newList = [];

            foreach($list as $key => $value) {
                $newList[$key] = $value;
            }

            $list = $newList;
        }

        if(key_exists('empty_option', $options) && $options['empty_option']===true) {
            $nList[''] = 'Selecione...';

            foreach ($list as $i => $v) {
                $nList[$i] = $v;
            }

            $list = $nList;
        }

        $options = $this->appendToOptions('class', 'form-control', $options);

        $html  = $this->openGroup($name, $label);
        $html .= parent::select($name, $list, $selected, $options);
        $html .= $this->closeGroup();

        return $html;
    }



} 