# UNIT

> UNIT is a platform for deploying purpose-built AI workers — each one trained for a specific workflow, ready to run on your team.

UNIT builds AI workers, not generic chatbots. Each worker has one job, does it end-to-end, and logs every step for human review. The platform is multi-tenant: an organization deploys a worker, connects its own credentials (e.g. Gmail), and the worker runs autonomously inside that tenant's account.

## Workers

@foreach ($workers as $worker)
- [{{ $worker['name'] }}]({{ $worker['url'] }}): {!! $worker['summary'] !!}
@endforeach
@if (count($upcomingWorkers) > 0)

In development, not yet publicly available:
@foreach ($upcomingWorkers as $name => $role)
- {{ $name }} ({{ $role }})
@endforeach
@endif

## Company

- [All workers]({{ route('public.workers.index') }})
- [About]({{ route('about') }})
- [Pricing]({{ route('pricing') }})
- [Blog]({{ route('blog') }})

## Blog posts

@foreach ($posts as $post)
- [{!! $post['title'] !!}]({{ $post['url'] }})
@endforeach

## Legal

- [Terms of Use]({{ route('terms') }})
- [Privacy Policy]({{ route('privacy') }})
