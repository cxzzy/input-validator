<?php
namespace wies\inputValidator;

class Validation 
{
    public function __construct() 
    {
        $this->errors = array();
        $request = new Request();

        $this->validationErrors = new Errors();
    }

    public function validate($fields) 
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($fields as $field => $rules) {
                if (!isset($_POST[$field])) {
                    return;
                }

                $this->errors[$field] = array();
                $this->validationErrors->prepareErrorArray($field);

                $rules = explode('|', $rules);

                foreach ($rules as $rule) {
                    $rule_value = explode(':', $rule);
                    $rule_value = (isset($rule_value[1])) ? $rule_value[1] : false;
                    $rule = str_replace(':'.$rule_value, '', $rule);

                    if ($rule == 'required') {
                        $this->required($field);
                    }

                    if ($rule == 'phone') {
                        $this->phone($field);
                    }

                    if ($rule == 'email') {
                        $error = self::email($field);
                    }

                    if ($rule == 'dutch_zipcode') {
                        $error = self::dutchZipcode($field, false);
                    }

                    if (preg_match('/^same/', $rule, $match)) {
                        $this->same($field, $rule_value);
                    }

                    if (preg_match('/^gt/', $rule, $match)) {
                        $this->gt(1, 2);
                    }

                    if (preg_match('/^min/', $rule, $match)) {
                        $this->min($field, $rule_value);
                    }

                    if (preg_match('/^max$/', $rule, $match)) {
                        $this->max($field, $rule_value);
                    }

                    if (preg_match('/^max_words$/', $rule, $match)) {
                        $this->maxWordCount($field, $rule_value);
                    }

                    if (isset($error)) {
                        $this->validationErrors->addError($field, $error);
                        unset($error);
                    }
                }
            }
        }
    }

    private function required($field) 
    {
        if (!isset($_POST[$field]) || $_POST[$field] == '') {
            $error = $field . ' is required';
            $this->validationErrors->addError($field, $error);
        }
    }

    private function maxWordCount($field, $max) 
    {
        if (str_word_count($_POST[$field]) > $max) {
            $error = 'Max words used';
            $this->validationErrors->addError($field, $error);
        }
    }

    private function min($field, $min) 
    {
        if (strlen($_POST[$field]) < $min) {
            $error = 'The length is too short';
            $this->validationErrors->addError($field, $error);
        }
    }

    private function max($field, $max) 
    {
        if (strlen($_POST[$field]) > $max) {
            $error = 'The length is too long';
            $this->validationErrors->addError($field, $error);
        }
    }

    private function gt($field_1, $field_2) 
    {
        if ($field_1 < $field_2) {
            $error = "$field_1 must be the greater than $field_2";
            $this->validationErrors->addError($field, $error);
        }
    }

    private function same($field_1, $field_2) 
    {
        if ($_POST[$field_1] != $_POST[$field_2]) {
            $error = $field_1 . ' must be the same as '. $field_2;
            $this->validationErrors->addError($field_1, $error);
        }
    }

    private function phone($field)
    {
        $phoneNumber = preg_replace('/[^0-9]/i', '', $_POST[$field]);
        $phoneNumberLength = strlen($phoneNumber);
        if ($phoneNumberLength < 7 || $phoneNumberLength > 10) {
            $error = "Phonenumber not valid";
            $this->validationErrors->addError($field, $error);
        }
    }

    // https://stackoverflow.com/a/12026863
    public static function email($field) 
    {
        if (!filter_var($_POST[$field], FILTER_VALIDATE_EMAIL)) {
            return 'E-mail not valid';
        }
    }

    public static function dutchZipcode($field, $returnCondition = false)
    {
        $remove = str_replace(' ', '', $_POST[$field]);
        $upper = strtoupper($remove);
        $rule = (preg_match("/^\W*[1-9]{1}[0-9]{3}\W*[a-zA-Z]{2}\W*$/", $upper));

        if ($returnCondition) {
            return $rule;
        } else if (!$rule) {
            return 'Zipcode is not valid';
        }
    }

    public function hasErrors() 
    {
        foreach ($this->validationErrors->getErrors() as $error) {
            if (!empty($error)) {
                return true;
            }
        }
    }

    public function errors() 
    {
        return $this->validationErrors;
    }
}

Class Errors
{
    public function __construct()
    {
        $this->errors = array();
    }

    public function prepareErrorArray($field)
    {
        $this->errors[$field] = array();
    }

    public function addError($field, $error)
    {
        array_push($this->errors[$field], $error);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function css($field)
    {
        if ($this->fieldHasError($field)) {
            return 'input-error';
        }
    }

    public function fieldHasError($field)
    {
        return (isset($this->errors[$field][0])) ? $this->errors[$field][0] : false;
    }

    public function first($field, $message = '')
    {
        $fieldError = $this->fieldHasError($field);

        if ($message == '') {
            return $fieldError;
        } elseif ($fieldError) {
            return '<p class="error">'. $message .'</p>';
        }
    }
}

class Request 
{
    public function __construct() {
    }

    public function post($field) 
    {
        return (isset($_POST[$field])) ? $_POST[$field] : false;
    }
}