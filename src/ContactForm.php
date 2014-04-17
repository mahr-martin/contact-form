<?php

namespace App\PublicModule\Components;

use Nette\Application\UI,
    Nette\Application\UI\Form,
    Nette\ComponentModel\IContainer,
    Nette\Mail\Message,
    Nette\Mail\SendmailMailer,
    Nette\Mail\SmtpMailer;

/**
 * Base contact form designed for footer.
 * 
 * @author Martin Mahr <ExtakCz@gmail.com>
 */
class ContactForm extends UI\Form {

    /**
     * Constructs the contact form.
     * 
     * @param \Nette\ComponentModel\IContainer $parent
     * @param string $name
     */
    public function __construct(IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        // Name
        $this->addText('name', 'Vaše jméno')
                ->setRequired('Zadejte prosím vaše jméno.');

        // Email
        $this->addText('email', 'Váš e-mail')
                ->setEmptyValue('@')
                ->setRequired('Zadejte prosím váš email')
                ->addRule(Form::EMAIL, 'E-mail má nesprávný tvar');

        // Message
        $this->addTextArea('message', 'Zpráva')
                ->setRequired('Zadejte prosím váš vzkaz.');

        // Submit button
        $this->addSubmit('send', 'Odeslat');

        $this->onSuccess[] = array($this, 'submitted');
    }

    /**
     * It's called after the contact form is submitted.
     * 
     * @param ContactForm $form
     */
    public function submitted($form) {
        try {
            $this->sendMail($form->getValues());
            $this->presenter->flashMessage('Zpráva úspěšně odeslána!');
            $this->presenter->redirect('this');
        } catch (\Nette\InvalidStateException $e) {
            $form->addError('Nepodařilo se odeslat e-mail, zkuste to prosím za chvíli.');
        }
    }

    /**
     * Send mail with user message.
     * 
     * @param array $values
     */
    private function sendMail($values) {
        $template = new \Nette\Templating\FileTemplate(__DIR__ . '/../emails/contactForm.latte');
        $template->registerFilter(new \Nette\Latte\Engine());
        $template->title = 'Contact form message';
        $template->values = $values;        
        
        $mail = new Message();
        $mail->setFrom($values['email'], $values['name'])
                ->addTo("mahr@effectiva.cz")
                ->setSubject('Contact form message')
                ->setBody($values['message'])
                ->setHtmlBody($template);

        //$mailer = new SendmailMailer();
        $mailer = new SmtpMailer(array(
            'host' => 'smtp.gmail.com',
            'username' => 'ExtakCz@gmail.com',
            'password' => 'Extak30207955',
            'secure' => 'ssl',
        ));
        $mailer->send($mail);
    }

}
