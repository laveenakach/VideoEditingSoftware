# Web-Based Video Editing Software

A browser-based video editing application built as part of a 24-hour development assessment for Titan Group Partners.  
The editor provides a clean, intuitive workflow similar to Kapwing.

---

## 🚀 Features

### Video Upload & Management
- Drag-and-drop and file selection upload
- Video preview with playback controls
- Automatic capture of video metadata (duration, resolution)

### Timeline & Trimming
- Start and end trim controls
- Accurate synchronization with video playback

### Text Overlay Editing
- Add text overlays to video
- Drag, resize, and reposition text
- Modify font size, color, and placement
- Control text appearance timing during playback

### Aspect Ratio & Resizing
- Preset output formats:
  - Landscape (16:9)
  - Vertical (9:16)
  - Square (1:1)
- Live preview of resizing changes
- Automatic overlay adjustments when formats change

### Rendering & Export
- Apply trims, overlays, and resizing during rendering
- Asynchronous export processing
- Export progress indication
- Downloadable final video file

### User Experience
- Clear loading and progress states
- Error handling for uploads and exports
- Simple, professional editor layout

---

## 🧠 System Architecture

- **Frontend:** Video editor UI with live preview
- **Backend:** Laravel-based API
- **Rendering Engine:** FFmpeg
- **Storage:** Structured edit instructions stored as JSON
- **Export Flow:**
  1. User edits are stored as structured instructions
  2. Export triggers async rendering
  3. FFmpeg applies trims, overlays, and resizing
  4. Final video is generated and made available for download

---

## ⚙️ Setup Instructions

### Requirements
- PHP 8+
- Laravel 12
- MySQL Database
- FFmpeg installed and available on system path

### Installation
```bash
git clone https://github.com/laveenakach/VideoEditingSoftware.git
cd video-editor
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
