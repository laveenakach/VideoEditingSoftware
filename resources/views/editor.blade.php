<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Video Editor</title>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
body { font-family: sans-serif; padding:20px; }
.drop { border:2px dashed #999; padding:30px; text-align:center; cursor:pointer; }
.aspect { max-width:600px; background:black; margin:15px 0; position:relative; }
.aspect video { width:100%; height:100%; object-fit:cover; }

.text-layer {
 position:absolute;
 top:40%; left:40%;
 cursor:move;
 padding:4px 8px;
 background:rgba(0,0,0,.4);
}

.aspect-16-9 { aspect-ratio:16/9 }
.aspect-9-16 { aspect-ratio:9/16 }
.aspect-1-1  { aspect-ratio:1/1 }
</style>
</head>

<body>

<div x-data="editor()" x-init="init()">

<!-- UPLOAD -->
<div class="drop"
 @drop.prevent="handleDrop($event)"
 @dragover.prevent
 @click="$refs.file.click()">
 Drop video or click
 <input type="file" x-ref="file" hidden accept="video/*" @change="loadVideo">
</div>

<!-- PREVIEW -->
<div x-show="videoUrl"
 class="aspect"
 :class="aspectClass">
 <video x-ref="video" :src="videoUrl" controls></video>

 <!-- TEXT OVERLAY -->
 <div class="text-layer"
  x-text="overlay.text"
  :style="overlayStyle"
  @mousedown="dragStart">
 </div>
</div>

<!-- CONTROLS -->
<div x-show="videoUrl">
 Trim:
 <input type="range" min="0" :max="duration" step="0.1" x-model.number="start">
 <input type="range" min="0" :max="duration" step="0.1" x-model.number="end">

 <br><br>

 Text:
 <input x-model="overlay.text" placeholder="Text">
 <input type="color" x-model="overlay.color">
 <input type="number" x-model.number="overlay.size">

 <br><br>

 Aspect:
 <select x-model="aspect">
  <option>16:9</option>
  <option>9:16</option>
  <option>1:1</option>
 </select>

 <br><br>

 <button @click="exportVideo()">Export</button>
</div>

<p x-show="loading">Rendering…</p>
<a x-show="download" :href="download" download>Download</a>

</div>

<script>
function editor(){
 return {
  file:null, videoUrl:null,
  start:0, end:0, duration:0,
  aspect:'16:9',
  loading:false, download:null,

  overlay:{
   text:'Hello',
   x:40, y:40,
   size:32,
   color:'#ffffff'
  },

  get aspectClass(){
   return {
    'aspect-16-9':this.aspect==='16:9',
    'aspect-9-16':this.aspect==='9:16',
    'aspect-1-1':this.aspect==='1:1'
   }
  },

  get overlayStyle(){
   return `
    top:${this.overlay.y}%;
    left:${this.overlay.x}%;
    font-size:${this.overlay.size}px;
    color:${this.overlay.color};
   `;
  },

  init(){
   this.$watch('start',v=>{ if(v>this.end) this.start=this.end })
  },

  handleDrop(e){
   this.loadVideo({ target:{ files:e.dataTransfer.files }})
  },

  loadVideo(e){
   this.file=e.target.files[0]
   this.videoUrl=URL.createObjectURL(this.file)
   this.$nextTick(()=>{
    let v=this.$refs.video
    v.onloadedmetadata=()=>{
     this.duration=v.duration
     this.end=v.duration
    }
   })
  },

  dragStart(e){
   let move = ev=>{
    this.overlay.x += ev.movementX/5
    this.overlay.y += ev.movementY/5
   }
   window.addEventListener('mousemove',move)
   window.addEventListener('mouseup',()=>window.removeEventListener('mousemove',move),{once:true})
  },

  exportVideo(){
   this.loading=true
   let f=new FormData()
   f.append('video',this.file)
   f.append('start',this.start)
   f.append('end',this.end)
   f.append('aspect',this.aspect)
   f.append('text',JSON.stringify(this.overlay))

   fetch('/export',{
    method:'POST',
    headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
    body:f
   }).then(r=>r.json()).then(d=>{
    this.download=d.download
    this.loading=false
   })
  }
 }
}
</script>

</body>
</html>
