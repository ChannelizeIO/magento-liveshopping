<?php
/**
 * Copyright 2024 Channelize.io. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Channelize\LiveShopping\Plugin;

class CsrfValidatorSkip
{
    /**
     * Skip the Csrf Validator
     *
     * @param \Magento\Framework\App\Request\CsrfValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ActionInterface $action
     */
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        if ($request->getModuleName() == 'liveshopping') {
            return; // Skip CSRF check
        }
        $proceed($request, $action);
    }
}
