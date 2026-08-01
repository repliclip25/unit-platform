# UNIT

> UNIT is the platform for deploying AI workers that complete real business work.

Instead of using one generic AI assistant for every task, organizations deploy specialized AI workers through UNIT. Each worker is trained for a single responsibility, follows a defined workflow, connects to the organization's existing tools, and logs every action for human review.

UNIT workers don't just answer questions, they execute work from start to finish.

## How UNIT Works

- Every AI worker owns a specific business responsibility.
- Workers connect securely to your existing tools and accounts.
- Each worker follows structured workflows instead of open-ended conversations.
- Every action is logged for transparency and human review.
- Organizations can deploy multiple workers across different business functions.

## AI Workers

@foreach ($workers as $worker)
### {!! $worker['name'] !!}: {!! $worker['role'] !!} ({!! $worker['status'] !!})

{!! $worker['description'] !!}

@if (!empty($worker['audienceList']))
{!! $worker['audienceIntro'] ?? '' !!}

@foreach ($worker['audienceList'] as $item)
- {!! $item !!}
@endforeach

{!! $worker['audienceOutro'] ?? '' !!}

@endif
@endforeach
### Coming Soon

@foreach ($upcomingWorkers as $name => $role)
- {!! $name !!}: {!! $role !!}
@endforeach

## Platform

UNIT is a multi-tenant platform.

Each organization deploys its own AI workers and connects its own credentials and business systems. Workers operate only within that organization's environment and never access data across tenants.

## Philosophy

Businesses don't need another chatbot.

They need AI workers that own operational work.

UNIT is built around specialized AI workers rather than general-purpose assistants. Every worker has one responsibility, one workflow, and one measurable outcome.

## Learn More

- {{ route('public.workers.index') }}
- {{ route('about') }}
- {{ route('pricing') }}
- {{ route('blog') }}
- {{ route('insurance') }}
- {{ route('compliance') }}

## Current Worker

@foreach ($workers as $worker)
- {!! $worker['name'] !!}: {{ $worker['url'] }}
@endforeach

## Legal

- Terms of Use
  {{ route('terms') }}

- Privacy Policy
  {{ route('privacy') }}
