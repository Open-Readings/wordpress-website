<?php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;



require_once OR_PLUGIN_DIR . 'vendor/autoload.php';

function sendBulkEmail($apiKey, $recipients, $subject, $content, $attachments)
{
    $mailersend = new MailerSend(['api_key' => $apiKey]);

    $email = (new EmailParams())
        ->setFrom('info@openreadings.eu')
        ->setFromName('Open Readings 2024')
        ->setRecipients($recipients)
        ->setSubject($subject)
        ->setHtml($content);
    if (!empty($attachments)) {
        $email->setAttachments($attachments);
    }
    $bulkEmailParams[] = $email;

    $mailersend->bulkEmail->send($bulkEmailParams);

}

// Function to get bulk email status
function getBulkEmailStatus($apiKey, $bulkEmailId)
{
    $mailersend = new MailerSend(['api_key' => $apiKey]);

    $status = $mailersend->bulkEmail->getStatus($bulkEmailId);

    // Process the status as needed
    // For example, you can echo the status or return it for further use
    echo 'Bulk Email Status: ' . $status;
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the form data
    $subject = $_POST["subject"];
    $recipients = $_POST["recipients"];
    $attachmentName = $_POST["attachment_name"];
    $attachmentList = $_POST["attachment_list"];
    $replaceWith = $_POST["replace_with"];
    $message = $_POST["message"];
    $attachment = $_POST["attachment"];
    $useTemplate = isset($_POST["use_template"]) ? $_POST["use_template"] : "No"; // Default to "No" if not set

    // Extracted from the provided code snippets
    $apiKey = get_option("or_mailer_api_key");
    $all_recipients = explode('\n', $_POST['recipients']);  // Replace with your actual API key
    $recipients = [];
    for ($i = 0; $i < count($all_recipients); $i++) {
        $recipients[] = (new Recipient($all_recipients[$i], $all_recipients));
    }
    $template = file_get_contents(OR_PLUGIN_DIR . 'mailer/OR_email_template.html');
    $attachments = [];
    if ($useTemplate == "Yes") {
        // Replace the placeholders in the template with the actual values
        $content = str_replace("[content]", $message, $template);
    } else {
        $content = $message;
    }

    echo '<script>alert("Sending email...")</script>';
    // Handle file uploads
    if (!empty($_FILES['attachments']['name'][0])) {
        foreach ($_FILES['attachments']['name'] as $key => $file_name) {
            $file_tmp = $_FILES['attachments']['tmp_name'][$key];
            $file_size = $_FILES['attachments']['size'][$key];

            $attachment = new Attachment(file_get_contents($file_tmp), $file_name);
            $attachments[] = $attachment;
        }
    }

    // Assuming you want to send bulk email on form submission
    //sendBulkEmail($apiKey, $recipients, $subject, $content, $attachments);

    echo '<script>alert("Email sent successfully")</script>';
    // You can also get the status of the bulk email if needed
    //getBulkEmailStatus($apiKey, 'bulk_email_id');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mailer Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input,
        textarea,
        button,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h2>Mailer Admin</h2>

        <label for="subject">Subject:</label>
        <input type="text" name="subject" required>

        <label for="recipients">Recipients:</label>
        <textarea type="text" name="recipients" required></textarea>

        <label for="message">Message:</label>
        <textarea name="message" required></textarea>

        <label for="attachment">Attachment:</label>
        <input type="file" name="attachment">

        <label for="use_template">Use Template?</label>
        <input type="checkbox" name="use_template" value="Yes">


        <button type="submit">Send</button>
    </form>

</body>

</html>