<?php

if ($recaptcha = \RemoteSystems\ReCaptcha::getInstance()) {
    ContactRequestHandler::$validators[] = [
        'id' => 'ReCaptcha',
        'field' => 'g-recaptcha-response',
        'required' => true,
        'errorMessage' => 'Please prove that you aren\'t a spam robot by completing the reCAPTCHA',
        'validator' => function ($response) use ($recaptcha) {
            $recaptchaResponse = $recaptcha->verify($response, $_SERVER['REMOTE_ADDR']);

            return $recaptchaResponse->isSuccess();
        }
    ];

    ContactRequestHandler::$excludeFields[] = 'g-recaptcha-response';
}
