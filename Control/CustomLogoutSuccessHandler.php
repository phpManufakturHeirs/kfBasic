<?php

namespace phpManufaktur\Basic\Control;

use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\HttpFoundation\Request;

class CustomLogoutSuccessHandler extends DefaultLogoutSuccessHandler
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler::onLogoutSuccess()
     */
    public function onLogoutSuccess(Request $request)
    {
        // get all parameters
        $parameters = $request->query->all();
        // set the target
        $target = (!isset($parameters['redirect']) && !empty($parameters['redirect'])) ? $parameters['redirect'] : $this->targetUrl;
        unset($parameters['redirect']);
        // build the parameter string
        $parameter_str = !empty($parameters) ? '?'.http_build_query($parameters) : '';
        // return the logout response
        return $this->httpUtils->createRedirectResponse($request, $target.$parameter_str);
    }
}
