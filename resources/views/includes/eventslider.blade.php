{{-- Campus Buddy Event Image Slider --}}
<div class="event-slider-section">
  <h2>Recent Events</h2>
  <div class="slider-container">
    <div class="slider-track">
      @php
      $imageDir = public_path('images/eventImage/');
      $images = [];
      if (is_dir($imageDir)) {
      foreach (['jpg','jpeg','png','gif','webp'] as $ext) {
      $images = array_merge($images, glob($imageDir . "*.$ext"));
      }
      }
      @endphp

      @if(empty($images))
      <p class="no-events">No event images found.</p>
      @else
      @foreach($images as $image)
      <div class="slide">
        <img src="{{ asset('images/eventImage/' . basename($image)) }}" alt="Event Image">
      </div>
      @endforeach
      @endif
    </div>
  </div>
</div>

<!-- FULL SCREEN IMAGE VIEWER -->
<div class="image-viewer" id="imageViewer">
  <span class="close-btn">&times;</span>
  <span class="nav-btn nav-prev">&#8249;</span>
  <span class="nav-btn nav-next">&#8250;</span>
  <img src="" alt="Full size event image">
</div>