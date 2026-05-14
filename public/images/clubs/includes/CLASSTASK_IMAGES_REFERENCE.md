# ClassTask Page - Images & Icons Reference

This document lists all images and icons used in the ClassTask page for easy tracking and updates.

## Hero Section Image
- **File**: `/Users/washimakram/Campus_Buddy/public/images/community/studygroup.jpg`
- **Alt Text**: "Class Tasks"
- **Usage**: Hero background banner at the top of the ClassTask page
- **Description**: Study group image showing students collaborating on academic tasks
- **Blade Reference**: `{{ asset('images/community/studygroup.jpg') }}`

## Section Icons (SVG-based)

### Assignment Section Icon
- **Type**: SVG (Inline)
- **Icon Representation**: Document/Checklist icon
- **Color**: Orange/Red (#ff6b6b)
- **Background**: Linear gradient from #ff6b6b to #ff8c00 with low opacity
- **SVG Path**: Document with checkmark and horizontal lines (representing tasks)
- **CSS Class**: `.assignment-icon`
- **Usage**: Appears next to "Assignments" section title

### Quiz Section Icon
- **Type**: SVG (Inline)
- **Icon Representation**: Question mark with circle (Help/Knowledge icon)
- **Color**: Blue (#6496ff)
- **Background**: Linear gradient from #6496ff to #00aaff with low opacity
- **SVG Path**: Circle with question mark (help/knowledge symbol)
- **CSS Class**: `.quiz-icon`
- **Usage**: Appears next to "Quizzes" section title

### Presentation Section Icon
- **Type**: SVG (Inline)
- **Icon Representation**: Presentation/Video play icon
- **Color**: Green (#64c850)
- **Background**: Linear gradient from #64c850 to #00c896 with low opacity
- **SVG Path**: Play button with monitor (presentation/video symbol)
- **CSS Class**: `.presentation-icon`
- **Usage**: Appears next to "Presentations" section title

## Buddy Mascot Icon
- **File**: `/Users/washimakram/Campus_Buddy/public/images/menuicons/Buddy.png`
- **Alt Text**: "Campus Buddy"
- **Usage**: Avatar image in the Buddy Tips cards (all sections)
- **Description**: Campus Buddy mascot character in circular white wrapper
- **Blade Reference**: `{{ asset('images/menuicons/Buddy.png') }}`
- **Size**: 35x35px display size

## Icon Color Scheme

| Type | Primary Color | Secondary Color | Icon Path |
|------|----------------|-----------------|-----------|
| Assignment | #ff6b6b | #ff8c00 | Document with checkmark |
| Quiz | #6496ff | #00aaff | Question mark circle |
| Presentation | #64c850 | #00c896 | Play button monitor |

## CSS Classes Reference

- `.assignment-icon` - Orange/red gradient background
- `.quiz-icon` - Blue gradient background
- `.presentation-icon` - Green gradient background
- `.section-icon` - Base styling for all section icons (70x70px, rounded)
- `.section-icon svg` - SVG element styling (42x42px)

## File Structure

```
/public/images/
├── community/
│   ├── studygroup.jpg          (Hero image)
│   ├── dashboardBG.jpg
│   ├── event.jpg
│   ├── post.jpg
│   └── workshop.jpg
└── menuicons/
    └── Buddy.png               (Mascot icon)

/resources/views/
└── classtask.blade.php         (Uses all above images)

/public/css/
└── classtask.css              (SVG icon styling)
```

## Notes
- All SVG icons are inline and use `stroke` styling (not filled)
- Buddy mascot uses a circular wrapper with white background
- Hero image uses full viewport width with dark overlay
- Icons are responsive and scale based on screen size
- Colors maintain consistency with the campus buddy brand palette

