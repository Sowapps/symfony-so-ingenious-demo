<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class EmailService {

    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface     $mailer
    ) {
        //	public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, MailerInterface $mailer, TwigService $twig, UrlGeneratorInterface $router, Security $security, StringHelper $stringHelper, array $configEmail) {
        //		parent::__construct($entityManager, $security);
    }

    //	public function getEmailMessage(int $emailMessageId, string $messageKey): EmailMessage {
    //		/** @var EmailMessageRepository $emailMessageRepo */
    //		$emailMessageRepo = $this->entityManager->getRepository(EmailMessage::class);
    //		$emailMessage = $emailMessageRepo->find($emailMessageId);
    //		if( !$emailMessage ) {
    //			throw new NotFoundHttpException('email.notFound');
    //		}
    //		if( $emailMessage->getOnlineExpireDate() && $emailMessage->getOnlineExpireDate() < new DateTime('now') ) {
    //			throw new NotFoundHttpException('email.onlineView.expired');
    //		}
    //		if( $emailMessage->getPrivateKey() !== $messageKey ) {
    //			throw new NotFoundHttpException('email.wrongKey');
    //		}
    //
    //		return $emailMessage;
    //	}

    //	public function createFromTemplate(string $subject, $purpose, $recipient, string $template, ?array $data): ?EmailMessage {
    //		if( $recipient instanceof User ) {
    //			$email = $recipient->getEmail();
    //		} elseif( is_array($recipient) ) {
    //			$email = $recipient[1];
    //		} else {
    //			$email = $recipient;
    //		}
    //		if( $purpose instanceof EmailSubscription ) {
    //			$emailSubscription = $purpose;
    //			$purpose = $emailSubscription->getPurpose();
    //		} else {
    //			$emailSubscription = $this->getSubscription($email, $purpose);
    //		}
    //		if( $emailSubscription->isDisabled() ) {
    //			// Unsubscribed, we don't send the mail
    //			return null;
    //		}
    //		$emailMessage = new EmailMessage();
    //		$emailMessage->setFromUserEmail('no-reply@contact.com');
    //		$emailMessage->setFromUserName($this->translator->trans('app.label'));
    //		if( $recipient instanceof User ) {
    //			$emailMessage->setToUser($recipient);
    //		} elseif( is_array($recipient) ) {
    //			$emailMessage->setToUserName($recipient[0]);
    //			$emailMessage->setToUserEmail($recipient[1]);
    //		} else {
    //			$emailMessage->setToUserEmail($recipient);
    //		}
    //		$emailMessage->setPurpose($purpose);
    //		$emailMessage->setSubject($this->translator->trans($subject, $data, self::DOMAIN));
    //		$emailMessage->setOnlineExpireDate(new DateTime(sprintf('+%d hours', $this->config['online_view']['expire_hours'])));
    //		$emailMessage->setPrivateKey($this->stringHelper->generateKey());
    //		$emailMessage->setTemplateHtml($template);
    //		$emailMessage->setData($this->convertEntitiesToReferences($data));
    //		$emailMessage->setSubscription($emailSubscription);
    //
    //		$data['email'] = null;
    //		$data['emailMessage'] = $emailMessage;
    //		$data['subscription'] = $emailSubscription;
    //
    //		$emailMessage->setBodyHtml($this->twig->render($template, $data));
    //
    //		$this->create($emailMessage);
    //
    //		return $emailMessage;
    //	}

    //	public function getSubscription(string $userEmail, string $purpose, bool $createMissing = true): EmailSubscription {
    //		$emailSubscriptionRepo = $this->getSubscriptionRepository();
    //		$emailSubscription = $emailSubscriptionRepo->findByEmail($userEmail, $purpose);
    //
    //		if( !$emailSubscription && $createMissing ) {
    //			$emailSubscription = $this->createSubscription($userEmail, $purpose);
    //		}
    //
    //		return $emailSubscription;
    //	}

    //	public function getSubscriptionRepository(): EmailSubscriptionRepository {
    //		return $this->entityManager->getRepository(EmailSubscription::class);
    //	}
    //
    //	public function createSubscription(string $userEmail, string $purpose): EmailSubscription {
    //		$emailSubscription = new EmailSubscription();
    //		$emailSubscription->setEmail($userEmail);
    //		$this->fillNewSubscription($emailSubscription, $purpose);
    //		$this->create($emailSubscription);
    //
    //		return $emailSubscription;
    //	}
    //
    //	public function fillNewSubscription($emailSubscription, $purpose): void {
    //		$emailSubscription->setPrivateKey($this->stringHelper->generateKey());
    //		$emailSubscription->setPurpose($purpose);
    //	}

    public function sendTestEmail($recipient): void {
        $email = new TemplatedEmail();
        $email
            ->subject('App Test Email')
            ->from(new Address('contact@sowapps.com', $this->translator->trans('app.label')))
            ->to($recipient)
            ->htmlTemplate('email/email.test.html.twig');

        $this->mailer->send($email);
    }

    //	public function send(EmailMessage $emailMessage): void {
    //		$email = new TemplatedEmail();
    //		$email
    //			->subject($emailMessage->getSubject())
    //			->from(new Address($emailMessage->getFromUserEmail(), $emailMessage->getFromUserName()))
    //			->to(new Address($emailMessage->getToUserEmail(), $emailMessage->getToUserName() ?? ''));
    //
    //		if( $emailMessage->getBodyText() ) {
    //			$email->text($this->formatContents($emailMessage->getBodyText(), $emailMessage));
    //		}
    //		if( $emailMessage->getBodyHtml() ) {
    //			if( $emailMessage->getTemplateHtml() ) {
    //				$email->htmlTemplate($emailMessage->getTemplateHtml());
    //			} else {
    //				$email->html($this->formatContents($emailMessage->getBodyHtml(), $emailMessage));
    //			}
    //		}
    //
    //		$data = [];
    //		$data['email'] = null;
    //		$data['emailMessage'] = $emailMessage;
    //		$data['emailViewUrl'] = $this->getEmailViewUrl($emailMessage);
    //		$data['subscription'] = $emailMessage->getSubscription();
    //
    //		if( $emailMessage->getData() ) {
    //			$data = array_merge($data, $this->convertReferencesToEntities($emailMessage->getData()));
    //		}
    //
    //		$email->context($data);
    //
    //		$this->mailer->send($email);
    //		$emailMessage->setSendDate();
    //		$this->update($emailMessage);
    //	}
    //
    //	public function formatContents($contents, EmailMessage $emailMessage): string {
    //		return strtr($contents, [static::TOKEN_VIEW_ONLINE => $this->router->generate('email_message_view', [
    //			'messageId'  => $emailMessage->getId(),
    //			'messageKey' => $emailMessage->getPrivateKey(),
    //		], UrlGeneratorInterface::ABSOLUTE_URL)]);
    //	}
    //
    //	public function getEmailViewUrl(EmailMessage $emailMessage): string {
    //		return $this->router->generate('email_message_view', [
    //			'messageId'  => $emailMessage->getId(),
    //			'messageKey' => $emailMessage->getPrivateKey(),
    //		], UrlGeneratorInterface::ABSOLUTE_URL);
    //	}
    //
    //	public function updateSubscription(EmailSubscription $emailSubscription) {
    //		if( !$this->entityManager->contains($emailSubscription) ) {
    //			$this->create($emailSubscription);
    //		} else {
    //			$this->update($emailSubscription);
    //		}
    //	}

}
