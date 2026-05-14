{{-- Universal Ask Buddy Card Component --}}
@props([
    'title' => '🤖 Need Help?',
    'description' => 'Chat with Buddy to get instant answers and personalized assistance.',
    'button_text' => 'Ask Buddy',
    'delay' => '0'
])

<div class="buddy-section reveal {{ $delay ? 'delay-' . $delay : '' }}">
    <div class="buddy-card" onclick="window.location.href='{{ route('buddy-chat') }}'">
        <h3>{{ $title }}</h3>
        <p>{{ $description }}</p>
        <a href="{{ route('buddy-chat') }}" class="btn">{{ $button_text }}</a>
    </div>
</div>
