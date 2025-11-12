# Fix PHP Upload Size Limits

## Problem
Your image file (8.47 MB) exceeded PHP's upload limit (8 MB), causing all POST data to be silently discarded.

## Solution

### Option 1: Restart Apache as Administrator (Recommended)
1. Open PowerShell **as Administrator** (Right-click → Run as Administrator)
2. Run: `Restart-Service Apache24 -Force`
3. The php.ini has already been updated with:
   - post_max_size = 50M
   - upload_max_filesize = 20M

### Option 2: Manually Edit php.ini
1. Open `C:\php\php.ini` in a text editor
2. Find and change these lines:
   ```
   post_max_size = 8M    →  post_max_size = 50M
   upload_max_filesize = 2M  →  upload_max_filesize = 20M
   ```
3. Save the file
4. Restart Apache

### Option 3: Just Use Smaller Images
The form now has **client-side validation** that prevents files larger than 5 MB from being uploaded.

## What's Been Fixed
✅ Client-side validation: Blocks files > 5 MB before submission
✅ Server-side validation: Shows helpful error message if upload is too large
✅ php.ini updated: Increased limits to 50M/20M (requires Apache restart)

## Testing
1. Try uploading with a **small image** (< 5 MB) - should work immediately
2. Try uploading with a **large image** (> 5 MB) - will be blocked with warning
3. After restarting Apache, larger files (up to 20 MB) will work

## Quick Fix
**Compress your dog image** before uploading:
- Use online tools like TinyPNG.com or Compressor.io
- Or resize the image to smaller dimensions (e.g., 1200x800px)
- Most dog photos work fine at 1-2 MB
