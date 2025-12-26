<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Video Editor</title>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
body { font-family: Arial; padding:20px; }
.aspect-box { max-width:600px; background:black; overflow:hidden; margin-bottom:10px; }
.aspect-16-9 { aspect-ratio:16/9; }
.aspect-9-16 { aspect-ratio:9/16; }
.aspect-1-1 { aspect-ratio:1/1; }
video { width:100%; height:100%; object-fit:cover; }
label { display:block; margin-top:10px; }
</style>
</head>

<body>

<h2>Simple Video Editor</h2>

<div x-data="editor()">

<!-- Upload -->
<input type="file" accept="video/*" @change="upload">

<!-- Preview -->
<div x-show="videoUrl"
     class="aspect-box"
     :class="{
       'aspect-16-9': aspect==='16:9',
       'aspect-9-16': aspect==='9:16',
       'aspect-1-1': aspect==='1:1'
     }">

  <video x-ref="video"
         :src="videoUrl"
         controls
         @play="forceStart"></video>
</div>

<!-- Trim Controls -->
<label>Trim Start: <span x-text="start.toFixed(1)"></span>s</label>
<input type="range" min="0" :max="duration" step="0.1"
       x-model.number="start"
       @input="jumpToStart">

<label>Trim End: <span x-text="end.toFixed(1)"></span>s</label>
<input type="range" min="0" :max="duration" step="0.1"
       x-model.number="end">

<!-- Aspect -->
<label>Aspect Ratio</label>
<select x-model="aspect">
  <option value="16:9">16:9</option>
  <option value="9:16">9:16</option>
  <option value="1:1">1:1</option>
</select>

<br><br>
<button @click="exportVideo">Export</button>

<p x-show="loading">Rendering…</p>

<a x-show="download" :href="download" download>Download Video</a>

</div>

<script>
function editor() {
  return {
    file: null,
    videoUrl: null,

    start: 0,
    end: 0,
    duration: 0,

    aspect: '16:9',
    loading: false,
    download: null,

    upload(e) {
      this.file = e.target.files[0];
      this.videoUrl = URL.createObjectURL(this.file);

      this.$nextTick(() => {
        const v = this.$refs.video;

        v.onloadedmetadata = () => {
          this.duration = v.duration;
          this.start = 0;
          this.end = v.duration;
          v.currentTime = 0;
        };

        // 🔴 stop at trim end
        v.ontimeupdate = () => {
          if (v.currentTime >= this.end) {
            v.pause();
          }
        };
      });
    },

    // 🔴 jump immediately to trim start
    jumpToStart() {
      const v = this.$refs.video;
      v.pause();
      v.currentTime = this.start;
    },

    // 🔴 force play from trim start
    forceStart() {
      const v = this.$refs.video;
      if (v.currentTime < this.start || v.currentTime > this.end) {
        v.currentTime = this.start;
      }
    },

    exportVideo() {
      this.loading = true;

      const f = new FormData();
      f.append('video', this.file);
      f.append('start', this.start);
      f.append('end', this.end);
      f.append('aspect', this.aspect);

      fetch('/export', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document
            .querySelector('meta[name=csrf-token]').content
        },
        body: f
      })
      .then(r => r.json())
      .then(d => this.poll(d.edit_id))
      .catch(() => alert('Export failed'));
    },

    poll(id) {
      const timer = setInterval(() => {
        fetch(`/api/export/status/${id}`)
          .then(r => r.json())
          .then(d => {
            if (d.status === 'completed') {
              this.download = d.output_path;
              this.loading = false;
              clearInterval(timer);
            }
          });
      }, 2000);
    }
  }
}
</script>

</body>
</html>
