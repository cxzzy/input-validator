# input-validator
Validate input in PHP and JavaScript

# 1. Validation rules
Validation rules as an array for post data. The key is the field name the value are the rules separated by a pipe.
```php
$validation->validate(array(
    'email' => 'required|email',
    'phone_number' => 'required|min:5',
    'first_name' => 'required',
    'last_name' => 'required',
    'password' => 'required|min:5',
    'repeat_password' => 'same:password'
));
```

# 2. Check for errors
```php
if (!$validation->hasErrors()) {
  echo 'There are validation errors.';
}
```

# 3. Display errors
```php
$errors->first('email', 'E-mail is required');
```
