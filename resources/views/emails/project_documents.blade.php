@php($projectName = $project->name ?? 'your investment')
<p>Hi {{ $account->name }},</p>

<p>Here are the latest documents available for <strong>{{ $projectName }}</strong>:</p>

<ul>
    @foreach ($documents as $document)
        <li>
            <a href="{{ $document->url }}">{{ $document->name ?? 'Document' }}</a>
        </li>
    @endforeach
</ul>

<p>If you have any questions, just reply to this email.</p>

<p>Thanks,<br/>JaeVee Investor Support</p>

