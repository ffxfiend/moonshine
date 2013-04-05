<?php
/**
 * This file contains functions to help build
 * form fields for use within the admin. This
 * will allow me to build a form field outside
 * of the formFields.php file.
 *
 * The formFields.php file will be updated with
 * these functions.
 *
 * @todo Finish building class.
 *
 * @author Jeremiah Poisson <jpoisson@igzactly.com>
 * @version 1.0
 */

class igz_form {

    protected $form_action;
    protected $form_method;
    protected $form_id;

    /* ***** Specific Field Variables ***** */
    protected $value 			= '';
    protected $jsFunction		= '';
    protected $name				= '';
    protected $id				= '';
    protected $label			= '';
    protected $title			= '';
    protected $field_pre		= '';
    protected $field_post		= '';
    protected $size				= '';
    protected $required			= false;
    protected $richTextHeight	= '';
    protected $cols				= '';
    protected $rows				= '';
    protected $maxCharacters	= '';

    protected $wrapper			= true;

    protected $picker_colors = array(
        array('190707','2a0a0a','3b0b0b','610b0b','8a0808','b40404','df0101','ff0000','fe2e2e','fa5858','f78181','f5a9a9','f6cece','f8e0e0','fbefef'),
        array('191007','2a1b0a','3b240b','61380b','8a4b08','b45f04','df7401','ff8000','fe9a2e','faac58','f7be81','f5d0a9','f6e3ce','f8ece0','fbf5ef'),
        array('181907','292a0a','393b0b','5e610b','868a08','aeb404','d7df01','ffff00','f7fe2e','f4fa58','f3f781','f2f5a9','f5f6ce','f7f8e0','fbfbef'),
        array('101907','1b2a0a','243b0b','38610b','4b8a08','5fb404','74df00','80ff00','9afe2e','acfa58','bef781','d0f5a9','e3f6ce','ecf8e0','f5fbef'),
        array('071907','0a2a0a','0b3b0b','0b610b','088a08','04b404','01df01','00ff00','2efe2e','58fa58','81f781','a9f5a9','cef6ce','e0f8e0','effbef'),
        array('071910','0a2a1b','0b3b24','0b6138','088a4b','04b45f','01df74','00ff80','2efe9a','58faac','81f7be','a9f5d0','cef6e3','e0f8ec','effbf5'),
        array('071918','0a2a29','0b3b39','0b615e','088a85','04b4ae','01dfd7','00ffff','2efef7','58faf4','81f7f3','a9f5f2','cef6f5','e0f8f7','effbfb'),
        array('071019','0a1b2a','0b243b','0b3861','084b8a','045fb4','0174df','0080ff','2e9afe','58acfa','81bef7','a9d0f5','cee3f6','e0ecf8','eff5fb'),
        array('070719','0a0a2a','0b0b3b','0b0b61','08088a','0404b4','0101df','0000ff','2e2efe','5858fa','8181f7','a9a9f5','cecef6','e0e0f8','efeffb'),
        array('100719','1b0a2a','240b3b','380b61','4b088a','5f04b4','7401df','8000ff','9a2efe','ac58fa','be81f7','d0a9f5','e3cef6','ece0f8','f5effb'),
        array('190718','2a0a29','3b0b39','610b5e','8a0886','b404ae','df01d7','ff00ff','fe2ef7','fa58f4','f781f3','f5a9f2','f6cef5','f8e0f7','fbeffb'),
        array('190710','2a0a1b','3b0b24','610b38','8a084b','b4045f','df0174','ff0080','fe2e9a','fa58ac','f781be','f5a9d0','f6cee3','f8e0ec','fbeff5')
    );

    protected $picker_baseColors = array(
        '000000','0b0b0b','151515','1c1c1c','2e2e2e','424242','585858','6e6e6e','848484','a4a4a4','bdbdbd','d8d8d8','e6e6e6','f2f2f2','ffffff'
    );

    /**
     * Sets some basic variables the form builder class needs
     * in order to work.
     *
     * @author Jeremiah Poisson
     * @param string $action
     * @param string $method
     * @param string $id
     * @return void
     */
    public function __construct($action = '',$method = '',$id = '') {
        $this->form_action = $action;
        $this->form_method = $method;
        $this->form_id = $id;
    }

    /**
     * Returns the opening html form string.
     *
     * @author Jeremiah Poisson
     * @return string
     */
    public function form_start() {
        return '<form action="' . $this->form_action . '" id="' . $this->form_id . '" method="' . $this->form_method . '" enctype="multipart/form-data">';
    }

    /**
     * Returns the closing html form string.
     *
     * @author Jeremiah Poisson
     * @return string
     */
    public function form_end() {
        return '</form>';
    }

    /**
     * This function will build a form field. The type is
     * determined by the $type variable. If this is empty
     * is will generate a basic text field. The attributes
     * for the field are passed in a string using this
     * format: attribute:value attribute:value,
     * etc...
     *
     * @author Jeremiah Poisson
     * @param string $type
     * @param string $options
     * @return string
     */
    public function field($type = '', $options = '') {

        if ($type == '') { return; }

        $attr = $this->parse_atts($options);

        $str = '';

        if ($this->wrapper) { $str .= $this->wrapper_start(); }

        $str .= $this->field_pre . " ";

        switch ($type) {

            case 'color':
                $str .= $this->color_field($attr);
                break;

            default:
                $str .= '<input type="' . $type . '" ';
                foreach ($attr as $k => $v) {
                    $str .= ' ' . $k . '="' . $v . '" ';
                }
                $str .= ' />';

        }


        $str .= $this->field_post;

        if ($this->wrapper) { $str .= $this->wrapper_end(); }

        return $str;

    }

    /**
     * This will generate a color picker field.
     *
     * @author Jeremiah Poisson
     * @param string $attr
     * @return string
     */
    public function color_field($attr) {

        ## Set the picker ID ##
        $colPikerID = "colpicker_" . $this->name;

        $str  = '';
        $str .= '&nbsp;&nbsp;<input type="text" ';
        foreach ($attr as $k => $v) {
            $str .= ' ' . $k . '="' . $v . '" ';
        }
        $str .= ' readonly="readonly" style="text-align: center; background-color: #D8D8D8" /> ';
        $str .= '<a href="Javascript://" onclick="color_picker(\'' . $colPikerID . '\');">';
        $str .= '<div id="' . $colPikerID . '_sample" ';
        $str .= 'style="float: left; width: 19px; height: 19px; border: 1px solid #CECECE; background-color: #' . $this->value . ';">&nbsp;</div></a>';



        $str .= '<div class="cns_colorpicker" id="' . $colPikerID . '" style="width: 240px; height: 242px;">';
        $str .= '<div class="cns_colorpicker_topbar clearfix">';
        $str .= '<div class="cns_colorpicker_color" style="background-color: #4444FF" onclick="cp_chooseColor(\'4444FF\',\'' . $colPikerID . '\');">&nbsp;</div> ';
        $str .= '<div style="float: left; width: 100px; padding-top: 3px;">&nbsp;&nbsp;';
        $str .= '<a href="Javascript://" onclick="cp_chooseColor(\'4444FF\',\'' . $colPikerID . '\');">Default Color</a></div>';
        $str .= '<div style="position: absolute; right: 10px; top: 8px;">';
        $str .= '<a href="Javascript://" onclick="cp_closePicker(\'' . $colPikerID . '\');">Close</a></div>';
        $str .= '</div>';

        for ($cpi = 0; $cpi < sizeof($this->picker_colors); $cpi++) {
            $str .= '<div class="cns_colorpicker_row clearfix">';
            foreach ($this->picker_colors[$cpi] as $v) {
                $str .= '<div class="cns_colorpicker_color" style="background-color: #' . $v . '" ';
                $str .= 'onclick="cp_chooseColor(\'' . strtoupper($v) . '\',\'' . $colPikerID . '\');">&nbsp;</div>';
            }
            $str .= '</div>';
        }

        $str .= '<div class="cns_colorpicker_spacer" style="height: 8px;">&nbsp;</div>';
        $str .= '<div class="cns_colorpicker_row clearfix">';
        foreach ($this->picker_baseColors as $v) {
            $str .= '<div class="cns_colorpicker_color" style="background-color: #' . $v . '" ';
            $str .= 'onclick="cp_chooseColor(\'' . strtoupper($v) . '\',\'' . $colPikerID . '\');">&nbsp;</div>';
        }
        $str .= '</div>';
        $str .= '</div>';

        return $str;

    }

    /**
     * Generates and returns the html markup needed
     * for the opening form wrappers in the admin.
     *
     * @author Jeremiah Poisson
     * @return string
     */
    public function wrapper_start() {

        $str  = '';
        $str .= '<div class="fieldwrapper">';
        $str .= '<div class="clearfix">';
        $str .= '<div class="fieldName">' . $this->label;
        $str .= $this->required ? ' <span class="required">(required)</span>"' : '';
        $str .= '</div>';
        $str .= '<div class="field">';

        return $str;

    }

    /**
     * Generates and returns the html markup needed
     * for the closing form wrappers in the admin.
     *
     * @author Jeremiah Poisson
     * @return string
     */
    public function wrapper_end() {

        $str  = '';
        $str  .= '</div>';
        $str  .= '</div>';
        $str  .= '</div>';

        return $str;

    }

    /**
     * Parses a string with attribute:value pairs generating
     * an array that will be returned and used to add atributes
     * to the form fields.
     *
     * @author Jeremiah Poisson
     * @param string $options
     * @return array
     */
    public function parse_atts($options) {
        $atts = array();
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $options = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $options);
        if ( preg_match_all($pattern, $options, $match, PREG_SET_ORDER) ) {
            // print_a($match);
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3])) {
                    $t = strtolower($m[3]);
                    if (isset($this->{$t})) { $this->{$t} = stripcslashes($m[4]); }
                    $atts[$t] = stripcslashes($m[4]);
                }
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) and strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }
        } else {
            $atts = ltrim($options);
        }

        // print_a($atts);
        return $atts;
    }

}
