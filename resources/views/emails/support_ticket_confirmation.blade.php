<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Support Ticket Created</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Support Ticket Created</h2>
        
        <p>Hello {{ $account->name }},</p>
        
        <p>Thank you for contacting us. We've received your support request and our team will get back to you as soon as possible.</p>
        
        <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0;"><strong>Ticket ID:</strong> {{ $ticket->ticket_id }}</p>
            <p style="margin: 5px 0 0 0;"><strong>Subject:</strong> {{ $ticket->subject }}</p>
            @if($project)
                <p style="margin: 5px 0 0 0;"><strong>Project:</strong> {{ $project->name }}</p>
            @endif
        </div>
        
        <p>You can view and reply to your ticket by logging into your investor dashboard.</p>
        
        <p style="margin-top: 30px;">Best regards,<br>The JaeVee Team</p>
    </div>
</body>
</html>

