<?php
namespace Logigator\Service;

use DI\Annotation\Inject;
use Logigator\Helpers\PathHelper;
use PHPMailer\PHPMailer\PHPMailer;

class SmtpService
{

	/**
	 * @Inject
	 * @var ConfigService
	 */
	private $configService;

	public function loadTemplate(string $filename, array $keyValuePairs): string {
		$content = file_get_contents(PathHelper::getPath($this->configService->getConfig('email_templates_path'), $filename));
		foreach ($keyValuePairs as $key => $value) {
			$content = str_replace('%%' . $key . '%%', $value, $content);
		}
		return $content;
	}

	public function sendMail(string $account,
	                         array $recipients,
	                         string $subject,
	                         string $body,
	                         string $replyTo = null,
	                         string $attachment = null,
	                         string $attachmentName = null): bool {
		$mail = new PHPMailer(true);

		//Server settings
		$mail->isSMTP();                                            // Set mailer to use SMTP
		$mail->Host       = $this->configService->getConfig("smtp")->{$account}->{"hostname"};          // Specify SMTP server
		$mail->SMTPAuth   = $this->configService->getConfig("smtp")->{$account}->{"authentication"};    // Enable SMTP authentication
		$mail->Username   = $this->configService->getConfig("smtp")->{$account}->{"username"};          // SMTP username
		$mail->Password   = $this->configService->getConfig("smtp")->{$account}->{"password"};          // SMTP password
		if((bool)$this->configService->getConfig("smtp")->{$account}->{"secure"} && $this->configService->getConfig("smtp")->{$account}->{"secure"} != 'false')
			$mail->SMTPSecure = $this->configService->getConfig("smtp")->{$account}->{"secure"};        // Enable TLS encryption, `ssl` also accepted
		$mail->Port       = $this->configService->getConfig("smtp")->{$account}->{"port"};              // TCP port to connect to
		$mail->CharSet = 'utf-8';
		//Recipients
		$mail->setFrom($this->configService->getConfig("smtp")->{$account}->{"emailAddress"}, $this->configService->getConfig("smtp")->{$account}->{"displayName"});
		foreach ($recipients as $recipient) {
			$mail->addAddress($recipient);
		}
		//ReplyTo
		if($replyTo)
			$mail->addReplyTo($replyTo);
		//Attachments
		if($attachment) {
			if($attachmentName)
				$mail->addAttachment($attachment, $attachmentName);
			else
				$mail->addAttachment($attachment);
		}
		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $body;
		$mail->send();
		return true;
	}
}
