<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
{{--  <script src="https://cdn.jsdelivr.net/gh/aframevr/aframe@1c2407b26c61958baa93967b5412487cd94b290b/dist/aframe-master.min.js"></script>
  <script src="https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar-nft.js"></script>--}}
  <script src="{{ URL::asset('arjs/aframe.min.js')}} "></script>
  <script src="{{URL::asset('arjs/aframe-ar.js')}}"></script>
  <script src="{{URL::asset('arjs/gesture-detector.js')}}"></script>
  <script src="{{URL::asset('arjs/gesture-handler.js')}}"></script>
  <script src="{{URL::asset('arjs/aframe-extras.loaders.min.js')}}"></script>
<!--  <script src="https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar.js"></script>
  <script src="https://fcor.github.io/arjs-gestures/gesture-detector.js"></script>
  <script src="https://fcor.github.io/arjs-gestures/gesture-handler.js"></script>
  <script src="https://rawgit.com/donmccurdy/aframe-extras/master/dist/aframe-extras.loaders.min.js"></script>-->
</head>
<script>
  AFRAME.registerComponent('vidhandler', {
    schema: {
      target: {type: 'string'}
    },
    init: function() {
      this.videoNodes = document.querySelectorAll(this.data.target)
    },
    tick: function() {
      if (this.el.object3D.visible == true) {
        if (!this.toggle) {
          this.toggle = true;
          for (let i = 0; i < this.videoNodes.length; i++) {
            this.videoNodes[i].play();
          }
        }
      } else {
        if (this.toggle) {
          for (let i = 0; i < this.videoNodes.length; i++) {
            this.videoNodes[i].pause();
          }
          this.toggle = false;
        }
      }
    }
  });

</script>
<body style="margin: 0; overflow: hidden">

<a-scene
  {{--device-orientation-permission-ui="enabled: false"--}}
  vr-mode-ui="enabled: false;"
  loading-screen="enabled: false;"
  arjs="trackingMethod: best; sourceType: webcam; debugUIEnabled: false;"
  id="scene"
  embedded
  gesture-detector
>

  {{--PRIKAZ MODELA PREKO MAREKRA--}}
  @foreach($model_markers as $marker)
    <a-marker type = "pattern" preset = "custom" url = "{{ $marker->file_marker }}">
      <a-entity
        position="0 0 0"
        gltf-model="{{$marker->video_path}}"
{{--        smooth="true"
        smoothCount="10"
        smoothTolerance=".01"
        smoothThreshold="5"
        raycaster="objects: .clickable"
        emitevents="true"
        cursor="fuse: false; rayOrigin: mouse;"--}}
      ></a-entity>
    </a-marker>

  @endforeach
  {{--//PRIKAZ MODELA PREKO MAREKRA--}}

  @foreach($video_markers as $marker)
    <a-assets>
      <video src="{{$marker->video_path}}"
             id="vid{{$marker->id}}"
             loop
             preload="auto"
             crossorigin="anonymous"
             playsinline
             autoplay
      ></video>
    </a-assets>

    <a-marker
      type = "pattern"
      preset = "custom"
      url = "{{ $marker->file_marker }}"
      vidhandler="target: #vid{{$marker->id}}"
      smooth="true"
      smoothCount="10"
      smoothTolerance=".01"
      smoothThreshold="5"
      raycaster="objects: .clickable"
      emitevents="true"
      cursor="fuse: false; rayOrigin: mouse;"
    >

      <a-video
        src="#vid{{$marker->id}}"
        scale="1 1 1"
        position="0 0.1 0"
        rotation='-90 0 0'
        class="clickable"
        gesture-handler
      >
      </a-video>
    </a-marker>
    @endforeach



  @foreach($markers as $marker)
    <a-marker type = "pattern" preset = "custom" url = "{{ $marker->file_marker }}">
     {{-- <a-entity position="0 0.5 0" scale="3 3 3" text="value: {{ $marker->text }};color: {{ $marker->color }} "></a-entity>--}}
      <a-entity {{--position="0 0.5 0" scale="3 3 3"--}} text="width: 2; lineHeight: 50; letterSpacing: 5; value: {{$marker->text}};color: {{$marker->color}}"></a-entity>
    </a-marker>
  @endforeach

  @foreach($image_markers as $marker)
  <a-assets>
    <a-img scale="2 2 2" src="{{$marker->video_path}}" crossorigin id="{{$marker->id}}"></a-img>
  </a-assets>

    <a-marker type = "pattern" preset = "custom" url = "{{ $marker->file_marker }}">
    <a-plane position='0 0.5 0' scale="2 2 2" width="2"  material='transparent:true;src:#{{$marker->id}}'></a-plane>
  </a-marker>
  @endforeach

<!-- define a camera inside the <a-marker-camera> -->

  <a-entity camera></a-entity>
</a-scene>

</body>


</html>
