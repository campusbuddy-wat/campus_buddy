@props([
    'alumni' => null, // Optional AlumniRegistration model
    'category' => '',
    'stagger' => null,
    'topBg' => null,
    'topImg' => null,
    'topImgClass' => 'field-img',
    'topImgStyle' => null,
    'badge' => 'ALUMNI',
    'badgeStyle' => null,
    'profileImg' => null,
    'profileImgClass' => 'profile-img',
    'profileImgStyle' => null,
    'cardCategory' => '',
    'title' => '',
    'subtitle' => null, // Optional sub-text like awards
    'details' => [], 
    'connectUrl' => '#',
    'rating' => '5.0',
    'name' => '',
    'containerClass' => 'field-img-container'
])

@php
if ($alumni) {
    $category = $category ?: $alumni->category;
    $cardCategory = $cardCategory ?: ucfirst(str_replace('-', ' ', $alumni->category));
    $name = $name ?: $alumni->full_name;
    $title = $title ?: ($alumni->current_position . ' @ ' . $alumni->company);
    $connectUrl = $connectUrl !== '#' ? $connectUrl : ($alumni->linkedin_url ?? '#');
    
    if (!$topImg) {
        if ($alumni->company_logo) {
            $topImg = asset('storage/' . $alumni->company_logo);
            $topImgClass = $alumni->top_img_class ?: 'img-contain-80';
            $containerClass = $alumni->container_class ?: 'card-top-logo-container';
            $topBg = $topBg ?: 'background: #ffffff;';
        } elseif ($alumni->card_bg_image) {
            $topImg = asset('storage/' . $alumni->card_bg_image);
            $topImgClass = $alumni->top_img_class ?: 'img-cover-full';
            $topImgStyle = $topImgStyle ?: 'width: 100%; height: 100%; object-fit: cover;';
        } else {
            $topImg = asset('images/alumni/alumni_tech_bg.png');
            $topImgClass = 'img-cover-full';
        }
    }
    
    // Manual overrides from database if not already provided via props
    $badge = $badge === 'ALUMNI' ? ($alumni->badge_text ?: $badge) : $badge;
    $badgeStyle = $badgeStyle ?: $alumni->badge_style;
    $topImgClass = $topImgClass === 'field-img' ? ($alumni->top_img_class ?: $topImgClass) : $topImgClass;
    $profileImgClass = $profileImgClass === 'profile-img' ? ($alumni->profile_img_class ?: $profileImgClass) : $profileImgClass;
    $subtitle = $subtitle ?: $alumni->subtitle;
    
    if (!$profileImg) {
        $profileImg = $alumni->profile_image ? asset('storage/' . $alumni->profile_image) : asset('images/alumni/profile_placeholder.png');
    }
    
    if (empty($details)) {
        $details = [
            ['icon' => 'fas fa-university', 'text' => $alumni->department],
            ['icon' => 'fas fa-graduation-cap', 'text' => 'Class of ' . $alumni->graduation_year]
        ];
    }
}
@endphp

<div class="alumni-card featured-card reveal animate-item up {{ $stagger ? $stagger : '' }}" data-category="{{ $category }}">
    <div class="card-top">
        @if($topBg)
            <div class="{{ $containerClass }}" style="{{ $topBg }}">
                <img src="{{ $topImg }}" alt="Logo" class="{{ $topImgClass }}" style="{{ $topImgStyle }}">
            </div>
        @else
            <img src="{{ $topImg }}" alt="Background" class="{{ $topImgClass }}" style="{{ $topImgStyle }}">
        @endif
        
        <div class="premium-badge" style="{{ $badgeStyle }}">{{ $badge }}</div>
        
        <div class="profile-img-wrap">
            <img src="{{ $profileImg }}" alt="{{ $name }}" class="{{ $profileImgClass }}" style="{{ $profileImgStyle }}">
        </div>
        
        <div class="card-category">{{ $cardCategory }}</div>
    </div>
    
    <div class="card-body">
        <h3>{{ $title }}</h3>
        @if($subtitle)
            <p class="card-subtitle">{{ $subtitle }}</p>
        @endif
        <div class="alumni-details">
            @foreach($details as $detail)
                <div class="detail-item">
                    <i class="{{ $detail['icon'] }}"></i>
                    <span>{{ $detail['text'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
    
    <div class="card-footer">
        <a href="{{ $connectUrl }}" target="_blank" rel="noopener noreferrer" class="connect-btn">Connect</a>
        <div class="rating">
            <span>{{ $rating }}</span>
            <div class="stars">
                @for($i = 0; $i < 5; $i++)
                    <i class="fas fa-star"></i>
                @endfor
            </div>
        </div>
        <div class="alumni-name alumni-name-footer">
            {{ $name }} <span class="verified-badge"><i class="fas fa-check"></i></span>
        </div>
    </div>
</div>
