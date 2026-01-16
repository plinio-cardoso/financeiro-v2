# SCSS Architecture

## Color System

### Primary Colors
- **Primary Blue (Text)**: `rgb(48, 85, 191)` - #3055bf
  - Used for button text, links, and active states
- **Primary Light (Background)**: `rgb(210, 222, 255)` - #d2deff
  - Used for button backgrounds
- **Hover State**: `rgb(195, 210, 255)` - Lighter hover
- **Active State**: `rgb(180, 198, 255)` - Pressed state

### Light Mode Colors (Clean & Friendly)
- **Background**: `rgb(249, 250, 251)` - Very light gray (gray-50)
- **Surface**: `rgb(255, 255, 255)` - Pure white cards and panels
- **Border**: `rgb(229, 231, 235)` - Subtle borders (gray-200)
- **Text**: `rgb(17, 24, 39)` - Dark text (gray-900)
- **Text Muted**: `rgb(107, 114, 128)` - Secondary text (gray-500)

### Dark Mode Colors (Neutral Dark Gray Theme)
- **Background**: `rgb(23, 23, 23)` - #171717 (Main background)
- **Surface**: `rgb(35, 35, 35)` - #232323 (Elevated surfaces like nav, cards)
- **Elevated**: `rgb(57, 57, 57)` - #393939 (More elevated elements)
- **Card**: `rgba(255, 255, 255, 0.03)` - Very subtle card overlay
- **Border**: `rgba(255, 255, 255, 0.08)` - Subtle borders
- **Text**: `rgb(249, 250, 251)` - Almost white (gray-50)
- **Text Muted**: `rgb(156, 163, 175)` - Secondary text (gray-400)

## Usage

### Tailwind Classes
```html
<!-- Primary button -->
<button class="bg-primary text-white">Button</button>

<!-- Primary light background -->
<div class="bg-primary-50">Light background</div>

<!-- Dark mode -->
<div class="bg-white dark:bg-dark-bg">
    <div class="bg-gray-100 dark:bg-dark-surface">Card</div>
</div>
```

### SCSS Variables
```scss
@use 'abstracts' as *;

.custom-component {
    background-color: $color-primary;

    @include dark {
        background-color: $color-dark-surface;
        border-color: $color-dark-border;
    }
}
```

## Structure
- `abstracts/` - Variables, mixins, functions
- `base/` - Typography and base styles
- `components/` - Reusable components (buttons, cards, forms)
- `layout/` - Layout utilities (container, grid)
- `utilities/` - Helper classes
- `theme/` - Tailwind overrides

## Important Notes
- Avoid duplicating Tailwind classes in SCSS
- Use BEM naming for custom components
- Dark mode uses class-based strategy
- Primary colors work in both light and dark modes
