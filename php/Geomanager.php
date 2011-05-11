<?php
    require_once dirname(__FILE__) . "/Geolocator/php/Geolocator.php";
 
    class Geomanager {
        var $geolocator;
        var $rules;

        function Geomanager($rules = null, $geoLocatorSettings=null) {
            if($rules !== null) $this->rules = $rules;
            $this->geolocator = new Geolocator($geoLocatorSettings);
        }

        function unsetRule($countryCode) {
            $rules[$countryCode] = null;
        }

        function setRule($countryCode, $settings) {
            if(isset($this->rules[$countryCode])) {
                $oldRules = $this->rules[$countryCode];
            } else {
                $oldRules = null;
            }

            $this->rules[$countryCode] = $settings;
            
            return $oldRules;
        }

        function process($rules = null, $ip = null) {
            if($rules === null) $rules = $this->rules;
            if($ip === null) $ip = $_SERVER['REMOTE_ADDR'];

            $cc = $this->geolocator->getCountryCode($ip);            
            if(isset($rules[$cc]) && is_array($rules[$cc])) {
                $rule = ($rules[$cc]);
            } else if(isset($rules['default']) && is_array($rules['default'])) { 
                $rule = ($rules['default']);
            } else {
                return false;
            }

            return $this->_action($rule);
        }   

        function _action($rule) { trigger_error("Geomanager class called without defined _action."); }
    }

    class Geocontent extends Geomanager {
        function Geocontent($default, $rules=null, $geoLocatorSettings=null) {
            parent::Geomanager($rules, $geoLocatorSettings);
            $this->setDefaultContent($default);
        }

        function setLocaleContent($countryCode, $content) {
            $this->setRule($countryCode, array('content'=>$content));
        }

        function setDefaultContent($content) {
            $this->setLocaleContent("default", $content);
        }

        function _action($array) {
            return $array['content'];
        }
    }

    class Georedirector extends Geomanager {
        function Georedirector($defaultURL, $defaultType=301, $rules=null, $geoLocatorSettings=null) {
            parent:Geomanager($rules, $geoLocatorSettings);
            $this->setDefaultURL($defaultURL, $defaultType);
        }

        function setLocaleURL($countryCode, $defaultURL, $defaultType=301) {
            $this->setRule($countryCode, array("url"=>$defaultURL, "code"=>$defaultType));
        }

        function setDefaultURL($defaultURL, $defaultType=301) {
            $this->setLocaleURL("default", $defaultURL, $defaultType);
        }

        function _action($array) {
            extract($array);
            if(!isset($code) || !is_numeric($code)) $code = 301;

            if(headers_sent()) {
                trigger_error("Geomanager: headers sent before redirect call.", E_USER_ERROR);
            }
                                        
            header("Location: {$url}", true, $code);
        }

    }
?>
