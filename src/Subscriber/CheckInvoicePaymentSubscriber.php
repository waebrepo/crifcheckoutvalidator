<?php

declare(strict_types=1);

namespace Geschenkkoerbe\CrifCheckoutValidator\Subscriber;

use Geschenkkoerbe\CrifCheckoutValidator\Service\CrifValidator;
use Geschenkkoerbe\CrifCheckoutValidator\Service\GeneralConfig;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\Checkout\Cart\Transaction\Struct\Transaction;
use Shopware\Storefront\Framework\Routing\StorefrontResponse;
use Shopware\Storefront\Framework\Twig\ErrorTemplateStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class CheckInvoicePaymentSubscriber implements EventSubscriberInterface
{
    private const CRIF_CHECK_REJECTED_MESSAGE = 'Crif check rejected';
    private RouterInterface $router;
    private GeneralConfig $generalConfig;
    private CrifValidator $crifValidator;

    public function __construct(
        RouterInterface $router,
        GeneralConfig $generalConfig,
        CrifValidator $crifValidator
    ) {
        $this->router = $router;
        $this->generalConfig = $generalConfig;
        $this->crifValidator = $crifValidator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CartConvertedEvent::class => 'crifCheck',
            KernelEvents::RESPONSE => 'onCheckoutOrderLoaded',
        ];
    }

    public function onCheckoutOrderLoaded(ResponseEvent $event): void
    {
        if ($event->getRequest()->getPathInfo() !== '/checkout/order') {
            return;
        }
        $response = $event->getResponse();
        if (!$response instanceof StorefrontResponse) {
            return;
        }
        $page = $response->getData()['page'];
        if (!$page instanceof ErrorTemplateStruct) {
            return;
        }
        if (!array_key_exists('exception', $page->getArguments())) {
            return;
        }
        $exception = $page->getArguments()['exception'];
        if (!$exception instanceof \Exception) {
            return;
        }
        if ($exception->getMessage() !== self::CRIF_CHECK_REJECTED_MESSAGE) {
            return;
        }
        $newResponse =
            new RedirectResponse($this->router->generate('frontend.checkout.confirm.page'), Response::HTTP_FOUND);
        $event->getRequest()->getSession()->getFlashBag()->add('danger', 'Leider ist bei der Bestellung ein Fehler aufgetreten.');
        $event->setResponse($newResponse);
    }

    /**
     * @throws \Exception
     */
    public function crifCheck(CartConvertedEvent $event): void
    {
        $lastTransaction = $event->getCart()->getTransactions()->last();
        if (!$lastTransaction instanceof Transaction) {
            throw new \Exception('CrifCheckoutValidator transaction is missing in function crifCheck');
        }
        if (!$this->isPaymentMethodConcerned($event)) {
            return;
        }
        if ($this->crifValidator->validateCustomer($event->getConvertedCart(), $event) === false) {
            throw new \Exception(self::CRIF_CHECK_REJECTED_MESSAGE);
        }
    }

    /**
     * @throws \Exception
     */
    private function isPaymentMethodConcerned(CartConvertedEvent $event): bool
    {
        $lastTransaction = $event->getCart()->getTransactions()->last();
        return in_array($lastTransaction->getPaymentMethodId(), $this->generalConfig->getPaymentMethodsToCheck());
    }
}
