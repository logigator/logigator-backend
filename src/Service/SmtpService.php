<?php
namespace Logigator\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Psr\Container\ContainerInterface;

class SmtpService extends BaseService
{
	public function __construct(ContainerInterface $container, $config) {
		parent::__construct($container, $config);
	}

	public function loadTemplate(string $path, array $keyValuePairs): string {
		$content = file_get_contents($path);
		foreach ($keyValuePairs as $keyValuePair) {
			$content = str_replace('%%' . $keyValuePair[0] . '%%', $keyValuePair[1], $content);
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

		try {
			//Server settings
			$mail->isSMTP();                                            // Set mailer to use SMTP
			$mail->Host       = $this->config["smtp"][$account]["hostname"];          // Specify SMTP server
			$mail->SMTPAuth   = $this->config["smtp"][$account]["authentication"];    // Enable SMTP authentication
			$mail->Username   = $this->config["smtp"][$account]["username"];          // SMTP username
			$mail->Password   = $this->config["smtp"][$account]["password"];          // SMTP password
			if((bool)$this->config["smtp"][$account]["secure"] && $this->config["smtp"][$account]["secure"] != 'false')
				$mail->SMTPSecure = $this->config["smtp"][$account]["secure"];        // Enable TLS encryption, `ssl` also accepted
			$mail->Port       = $this->config["smtp"][$account]["port"];              // TCP port to connect to
			$mail->CharSet = 'utf-8';

			//Recipients
			$mail->setFrom($this->config["smtp"][$account]["emailAddress"], $this->config["smtp"][$account]["displayName"]);
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
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			return false;
		}
	}
}
