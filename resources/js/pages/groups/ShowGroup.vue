<template>
  <div>
    <b-tabs content-class="mt-3">

    <b-tab :title="$t('information')" active>
      <div class="row mt-5">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title">{{$t('title_show_group')}}</div>
              <div class="card-tools">
              </div>
            </div>

            <b-table responsive hover :items="[group]" :fields="fieldsGroup">

              <template v-slot:cell(group_details)="data">
                <a v-b-popover.hover.top="$t('group_detail')" :href="data.item.path_word" target="_blank" >
                  <i class="fa fa-download"></i>
                </a>
              </template>



              <template v-slot:cell(action)="data">
                <a href="#" v-on:click="editModal(data.item)">
                  <i class="fa fa-edit blue"></i>
                </a>
              </template>



            </b-table>
            <!-- /.card-header -->
            <!-- /.card -->
          </div>
        </div>
      </div>
    </b-tab>
    <b-tab :title="$t('markers_title_in_group')">
      <div class="row mt-5">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title" v-if="group.markers && group.markers.length>0">{{$t('markers_title_card_in_group')}}</div>
              <div class="card-title" v-else>{{$t('markers_title_in_group_empty')}}</div>
              <i class="fa fa-plus-square" @click="newModal"></i>
              <div class="card-tools">

                <button  v-b-popover.hover.top="$t('save_markers_in_group')" class="btn btn-outline-primary" @click="openModalConnectMarker"><i class="fa fa-link"></i></button>
<!--                <button class="btn btn-outline-primary" @click="openModalAutocomplete"><i class="fas fa-connectdevelop"></i></button>-->
              </div>
            </div>

            <b-table  responsive hover :items="group.markers" :fields="fieldsMarkers">

<!--              <template v-slot:cell(name)="data">
                <router-link :to="{ name: 'showMarker', params: {id: data.item.id } }">
                  {{ data.value}}
                </router-link>
              </template>-->

              <template v-slot:cell(marker)="data">
                <a  href="#" @click="downloadMarker(data.item.image_marker,data.item.id)">
                  <i class="fa fa-download blue"></i>
                </a>
              </template>

              <template v-slot:cell(action)="data">

                <a v-b-popover.hover.top="$t('marker_view')" href="#" @click="getPictureForMarker(data.item.video_path,data.item.type,data.item.text)">
                  <i class="fa fa-eye "></i>
                </a>
                <a v-b-popover.hover.top="$t('marker_download')" href="#" @click="downloadContentMarker(data.item.video_path,data.item.type,data.item.name,data.item.id)">
                  <i class="fa fa-download"></i>
                </a>

                <a  v-b-popover.hover.top="$t('marker_trash_in_group')" href="#" @click="deleteMarker(data.item)">
                  <i class="fa fa-trash red"></i>
                </a>

                <a v-b-popover.hover.top="$t('marker_clone')" href="#" @click="cloneMarker(data.item)">
                  <i class="fa fa-clone "></i>
                </a>
<!--                <a href="#" v-on:click="deleteaMarker(data.item)"><i class="fa fa-trash red"></i></a>-->
              </template>



            </b-table>
            <!-- /.card-header -->
            <!-- /.card -->
          </div>
        </div>
      </div>
    </b-tab>


  </b-tabs>

    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="openPicture" tabindex="-1" role="dialog" aria-labelledby="addNewLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" v-show="!editmode" >{{$t('preview')}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="imagePreviewWrapper">
              <img v-if="type==='picture'" :src="src_path_in_modal" height="250px" width="250px">
              <video v-if="type==='video'" :src="src_path_in_modal" controls id="video_preview" height="250px" width="250px"/>
              <model-gltf v-if="type==='models'" :src="src_path_in_modal"></model-gltf>
              <textarea
                v-if="type==='text'"
                :value="text_marker"
                cols="50"
                rows="10"
                readonly
              >
                  </textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" @click="stopVideo" id="close_window_preview" data-dismiss="modal">{{$t('back')}} </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="addNew2" tabindex="-1" role="dialog" aria-labelledby="addNewLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" v-show="editmode" id="addNewLabel">{{$t('update')}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form enctype="multipart/form-data" @submit.prevent="updateGroup()">

            <div class="modal-body">
              <div class="form-group">
                <label for="name">{{$t('name')}}</label>
                <input v-model="formGroup.name" type="text" name="name"  :placeholder="$t('name')" class="form-control" :class="{ 'is-invalid': formGroup.errors.has('name') }">
                <has-error :form="formGroup" field="name"></has-error>
              </div>

              <div class="form-group">
                <label for="name">{{$t('description')}}</label>
                <textarea v-model="formGroup.description"
                          type="text"
                          cols="30"
                          rows="10"
                          name="description"
                          :placeholder="$t('description')"
                          class="form-control"
                          :class="{ 'is-invalid': formGroup.errors.has('description') }">
                  </textarea>
                <has-error :form="formGroup" field="description"></has-error>
              </div>

              <div v-if="formGroup.path_word" class="form-group">

                <label>{{$t('current_file_group')}}: </label>

                <a :href="formGroup.path_word" target="_blank" >
                  <i class="fa fa-download"> {{formGroup.name}}</i>
                </a>

              </div>

              <div class="form-group">
                <label for="word">{{$t('wordUpload')}}</label>

                <b-form-file
                  id="word"
                  ref="uploadWord"
                  :file-name-formatter="formatNames"
                  v-model="wordDoc"
                  class="form-control"
                  :class="{ 'is-invalid': formGroup.errors.has('wordDoc') }"
                  :placeholder="$t('file_selected')"
                  :browse-text="$t('browse_file')"
                  :drop-placeholder="$t('drop_file')"
                ></b-form-file>
                <p class="small">{{$t('format_word')}}</p>
                <has-error :form="formGroup" field="wordDoc"></has-error>
              </div>


            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">{{$t('back')}} </button>
              <v-button :loading="formGroup.busy"  v-show="editmode" type="submit" class="btn btn-success">{{$t('update')}}</v-button>
              <v-button :loading="formGroup.busy"  v-show="!editmode" type="submit" class="btn btn-primary">{{$t('save')}}</v-button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="addNew" tabindex="-1" role="dialog" aria-labelledby="addNewLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" v-show="!editmode" id="addNewLabel">{{$t('save_marker')}}</h5>
            <h5 class="modal-title" v-show="editmode" id="addNewLabel">{{$t('update_marker')}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form enctype="multipart/form-data" @submit.prevent="editmode ? updateMarker() : createMarker()">

            <div class="modal-body">

              <div class="form-group" v-if="form.is_clone">
                <div class="row">
                  <div class="col-md-6">
                    <label for="name">{{$t('name')}}</label>
                    <input disabled v-model="form.name" type="text" name="name" id="name" :placeholder="$t('name_placeholder')" class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">

                  </div>
                  <div class="col-md-6">
                    <label for="name">{{$t('other_name')}}</label>
                    <input v-model="form.other_name" type="text" name="name" id="other_name" :placeholder="$t('other_name_placeholder')" class="form-control" :class="{ 'is-invalid': form.errors.has('other_name') }">

                  </div>
                </div>
                <has-error :form="form" field="name"></has-error>
              </div>

              <div class="form-group" v-else>
                <label for="name">{{$t('name')}}</label>
                <input v-model="form.name" type="text" name="name" id="name" :placeholder="$t('name_placeholder')" class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                <has-error :form="form" field="name"></has-error>
              </div>

              <div class="form-group">
                <label for="description">{{$t('description')}}</label>
                <textarea v-model="form.description"
                          type="text"
                          cols="30"
                          rows="5"
                          name="description"
                          id="description"
                          :placeholder="$t('description_placeholder')"
                          class="form-control"
                          :class="{ 'is-invalid': form.errors.has('description') }">
                  </textarea>
                <has-error :form="form" field="description"></has-error>
              </div>

              <b-form-group v-show="!editmode"  v-slot="{ ariaDescribedby }">
                <label >{{$t('type')}}</label>
                <b-form-radio-group
                  id="radio-group-1"
                  v-model="form.type"
                  :options="options"
                  :aria-describedby="ariaDescribedby"
                  name="radio-options"
                ></b-form-radio-group>
                <has-error :form="form" field="selected"></has-error>
              </b-form-group>

              <div v-show="form.type==='text'">
                <div class="form-group">
                      <textarea
                        id="text"
                        v-model="form.text"
                        :class="{ 'is-invalid': form.errors.has('text') }"
                        :placeholder="$t('text')"
                        class="form-control"
                      >
                      </textarea>
                  <has-error :form="form" field="text"></has-error>
                </div>

                <!--                <div class="form__field">
                  <div class="form__label">
                    <label for="color_picker">{{$t('color_picker')}}</label>
                  </div>
                  <div class="form__input">
                    <v-swatches
                      id="color_picker"
                      v-model="form.color"
                      show-fallback
                      fallback-input-type="color"
                      :class="{ 'is-invalid': form.errors.has('color') }"
                      popover-x="left"
                    ></v-swatches>
                    <has-error :form="form" field="color"></has-error>
                  </div>
                </div>-->

              </div>

              <div v-show="form.type==='video'">

                <div class="form-group">
                  <b-form-file
                    id="video"
                    ref="uploadfile"
                    :file-name-formatter="formatNames"
                    :class="{ 'is-invalid': form.errors.has('video') }"
                    v-model="attachments"
                    :placeholder="$t('file_selected')"
                    :browse-text="$t('browse_file')"
                    :drop-placeholder="$t('drop_file')"
                  ></b-form-file>
                  <p class="small">{{$t('format_video')}}</p>
                  <has-error :form="form" field="video"></has-error>
                </div>


              </div>

              <div v-show="form.type==='picture'">

                <div class="form-group">
                  <div
                    v-if="previewImage"
                    class="imagePreviewWrapper"
                    :style="{ 'background-image': `url(${previewImage})` }"
                    @click="selectImage">
                  </div>

                  <b-form-file
                    id="picture"
                    ref="uploadfile2"
                    :file-name-formatter="formatNames"
                    v-model="picture"
                    :class="{ 'is-invalid': form.errors.has('picture') }"
                    :placeholder="$t('file_selected')"
                    :browse-text="$t('browse_file')"
                    :drop-placeholder="$t('drop_file')"
                    @input="pickFile">
                    ></b-form-file>
                  <p class="small">{{$t('format_picture')}}</p>
                  <has-error :form="form" field="picture"></has-error>
                </div>


              </div>

              <div v-show="form.type==='models'">

                <div class="form-group">
                  <label for="models">{{$t('models')}}</label>
                  <b-form-file
                    id="models"
                    ref="uploadfile3"
                    :file-name-formatter="formatNames"
                    v-model="models"
                    :class="{ 'is-invalid': form.errors.has('models') }"
                    :placeholder="$t('file_selected')"
                    :browse-text="$t('browse_file')"
                    :drop-placeholder="$t('drop_file')"
                  ></b-form-file>
                  <p class="small">{{$t('format_model')}}</p>
                  <has-error :form="form" field="models"></has-error>
                </div>


              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">{{$t('back')}} </button>
              <v-button :loading="busy"  v-show="editmode" type="submit" class="btn btn-success">{{$t('update')}}</v-button>
              <v-button :loading="busy"  v-show="!editmode" type="submit" class="btn btn-primary">{{$t('save')}}</v-button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!--Autocomplete groups-->
    <div class="modal fade" id="connect_marker" tabindex="-1" role="dialog" aria-labelledby="addNewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addNewMarkers">{{$t('save_markers_in_group')}}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form @submit.prevent="saveMarkersInGroup()">
          <div class="modal-body">

            <div class="form-group">
              <vue-typeahead-bootstrap
                :data="markers"
                v-model="markersSearch"
                size="lg"
                ref="typehead"
                :class="{ 'is-invalid': formConnectMarkerAndGroup.errors.has('marker_id') }"
                :serializer="s => s.name"
                :placeholder="$t('input_name_marker')"
                :disableSort="true"
                @hit="selectedMarkers = $event"
              ></vue-typeahead-bootstrap>
              <has-error :form="formConnectMarkerAndGroup" field="marker_id"></has-error>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">{{$t('modal_back')}}</button>
            <button type="submit" class="btn btn-primary">{{$t('modal_save')}}</button>
          </div>

        </form>

      </div>
    </div>
  </div>

  </div>
</template>

<script>

import {ModelGltf}  from 'vue-3d-model';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap'
import moment from "moment";
import VSwatches from 'vue-swatches'
// Import the styles too, globally
import "vue-swatches/dist/vue-swatches.css"

export default {
  components:{
    VueTypeaheadBootstrap,
    ModelGltf,
    VSwatches
  },
  data() {
    return {
      wordDoc:[],
      editmode:false,
      markersSearch:'',
      selectedMarkers: null,
      busy:false,
      text_marker:null,
      type:null,
      src_path_in_modal:null,
      previewImage: null,
      attachments: [],
      picture:[],
      models:[],
      formFile: new FormData,
      options:[
        {
          text:this.$t('text'),
          value:'text'
        },
        {
          text:this.$t('video'),
          value:'video'
        },
        {
          text:this.$t('picture'),
          value:'picture'
        },
        {
          text:this.$t('models'),
          value:'models'
        },
      ],
      group: {},
      markers:[],

      fieldsGroup: [
        {
          label: '#',
          key: 'index',
        },
        {
          label: this.$t('name') ,
          key: 'name',
          sortable: true,
          stickyColumn:true,
        },
        {
          label: this.$t('description') ,
          key: 'description',
          sortable: true,
          stickyColumn:true,
        },

        {
          label: this.$t('created_at'),
          key: 'updated_at',
          sortable: true,
          formatter: (value) => {
            return moment(value).format('DD.MM.YYYY');
          }
        },
        {
          label: this.$t('markers_count'),
          key: 'markers_count',
        },
        {
          label: this.$t('group_details'),
          key: 'group_details',
        },
        {
          label: this.$t('action'),
          key:'action',

        },
      ],

      fieldsMarkers: [
        {
          key: 'select',
          stickyColumn: true,
        },
        {
          label: '#',
          key: 'index',
        },
        {
          label: this.$t('name') ,
          key: 'name',
          sortable: true,
          stickyColumn:true,
        },
        {
          label: this.$t('created_at'),
          key: 'updated_at',
          sortable: true,
          formatter: (value) => {
            return moment(value).format('DD.MM.YYYY');
          }
        },
        {
          label: this.$t('marker_picture'),
          key: 'marker',
        },
        {
          label: this.$t('action'),
          key:'action',

        },
      ],

      formGroup: new Form({
        id:'',
        name : '',
        description:'',
        updated_at: '',
        wordDoc:'',
        path_word:''
      }),

      form: new Form({
        id:'',
        name : '',
        description:'',
        updated_at: '',
        //selected:'text',
        text:'',
        color:'#000000',
        type:'text',
        url_video:'',
        selected: [],
        selectAll: false,
        group_id:'',
        is_clone:false,
        clone:'',
        other_name:''
      }),

      formConnectMarkerAndGroup: new Form({
          marker_id:null,
          group_id : null,
      }),

    }
  },
  methods:{

    saveMarkersInGroup() {

      this.$Progress.start();
      this.formConnectMarkerAndGroup.group_id=this.$route.params.id;
      this.formConnectMarkerAndGroup.marker_id=this.selectedMarkers.id;

      this.formConnectMarkerAndGroup.post('/api/marker-group-merge')
        .then((data) => {
          if(data.data.success){
            swal.fire(data.data.data, data.data.message, "success");
          }else {
            swal.fire(data.data.data, data.data.message, "error");
          }
          Fire.$emit('AfterCreate');
          $('#connect_marker').modal('hide');
          this.$Progress.finish();

        }).catch(error => {

        if (error.response.status == 422){

          this.formConnectMarkerAndGroup.errors.errors = error.response.data.errors;

        }
      })
    },

    async cloneMarker(marker){

      await axios.get('/api/check-name?name='+marker.name)
        .then(response => {
          this.new_name_marker = response.data;
        });

      this.editmode = false;
      this.deleteClassModal();
      this.form.fill(marker);
      this.form.name=this.new_name_marker;
      this.form.type=null;
      this.form.is_clone=true;
      this.form.selected=[];
      this.form.selectAll=false;

      $('#addNew').modal('show');
    },

    deleteClassModal(){
      $('.is-invalid').removeClass("is-invalid");
      $(".invalid-feedback").removeClass("invalid-feedback");
      $(".help-block").text('');
    },

    deleteMarker(marker){
      swal.fire({
        title:  this.$t('delete_modal_title')+ ' "'+marker.name+'" ?',
        text: this.$t('delete_modal_text') ,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: this.$t('confirmButtonText'),
        cancelButtonText: this.$t('cancelButtonText')
      }).then((result) => {

        // Send request to the server
        if (result.value) {
          this.form.marker_id=marker.id;
          this.form.group_id= this.$route.params.id
          this.form.delete('/api/marker_group/'+marker.id+'/'+this.$route.params.id).then((data)=>{
            if(data.data.success){
              swal.fire(
                data.data.data,
                data.data.message,
                'success'
              )
            }else{
              swal.fire(
                data.data.message,
                data.data,
                "error");
            }

            Fire.$emit('AfterCreate');
          }).catch(()=> {
            swal.fire(this.t('error'),this.t('error_message'), "warning");
          });
        }
      })
    },

    stopVideo(){
      let video = document.getElementById("video_preview");
      video.pause();
      video.currentTime = 0;
    },

    newModal(){
      this.deleteClassModal();
      this.editmode = false;
      this.form.reset();
      $('#addNew').modal('show');
    },

    pickFile () {
      let input = this.$refs.uploadfile2
      let file = input.files
      if (file && file[0]) {
        let reader = new FileReader
        reader.onload = e => {
          this.previewImage = e.target.result
        }
        reader.readAsDataURL(file[0])
        this.$emit('input', file[0])
      }
    },

    getPictureForMarker(path,type,text){
      this.type=type;
      this.text_marker=text;
      this.src_path_in_modal=path;
      $('#openPicture').modal('show');
    },

    selectImage () {
      this.$refs.uploadfile2.click()
    },


    downloadPictureMarkerFromGroup(id,group_name) {
      axios({
        method: 'get',
        url: '/api/group/' + id + '/export-marker-picture',
        responseType: 'arraybuffer',

      }).then(function (response) {

        let blob = new Blob([response.data], {type: 'application/pdf'});
        let link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = group_name+".pdf";
        link.click();
      })

    },

    async autoComplete(query){
      const group = this.$route.path.split('/')[2];
      axios.get('/api/markers/' + '?q=' + query + '&nogroup=' + group).then(({data}) => {
        this.markers  = data;
      });

    },

    openModalConnectMarker() {
      $('#connect_marker').modal('show');
    },

    createMarker() {
      this.busy=true;
      this.$Progress.start();
      this.formFile.append('models', this.models);
      this.formFile.append('picture', this.picture);
      this.formFile.append('video', this.attachments);
      this.formFile.append('id', this.form.id);
      this.formFile.append('name', this.form.name);
      this.formFile.append('description', this.form.description);
      this.formFile.append('updated_at', this.form.updated_at);
      this.formFile.append('text', this.form.text);
      this.formFile.append('color', this.form.color);
      this.formFile.append('clone', this.form.is_clone);
      this.formFile.append('other_name', this.form.other_name);
      this.formFile.append('type', this.form.type);
      this.formFile.append('group_id', this.$route.params.id);

      /*const config = {headers: {'Content-Type': 'multipart/form-data'}};*/

      axios.post('/api/marker/insert-in-group', this.formFile).then(data => {
        //console.log(data);
        if(data.data.success){
          swal.fire({
            type: 'success',
            title: data.data.message
          });
          Fire.$emit('AfterCreate');
          $('#addNew').modal('hide');
          this.$refs.uploadfile.reset();
          this.$refs.uploadfile2.reset();
          this.$refs.uploadfile3.reset();
          this.attachments=[];
          this.picture=[];
          this.models=[];
          this.previewImage=null;
          this.formFile= new FormData;
          this.form.reset();
        }
        else {
          swal.fire(
            data.data.data,
            data.data.message,
            "error");

          $('#addNew').modal('hide');
          this.formFile = new FormData;
          this.$refs.uploadfile.reset();
          this.$refs.uploadfile2.reset();
          this.$refs.uploadfile3.reset();
          this.attachments=[];
          this.previewImage=null;
          this.picture=[];
          this.models=[];

        }
        this.$Progress.finish();
        this.busy=false;

      })
        .catch(error => {
          if (error.response.status == 422){
            this.form.errors.errors = error.response.data.errors;
            this.busy=false;
          }
        });
    },

    formatNames(files) {
      if (files.length === 1) {
        return files[0].name
      } else if (files.length === 2 || files.length === 3 || files.length === 4) {
        return `${files.length} odabrane datoteke`
      } else {
        return `${files.length} odabranih datoteka`
      }
    },

    downloadMarker(path,id) {
      axios({
        url:window.location.origin+'/'+ path,
        method: 'GET',
        responseType: 'blob',
      }).then((response) => {
        var fileURL = window.URL.createObjectURL(new Blob([response.data]));
        var fURL = document.createElement('a');

        fURL.href = fileURL;
        fURL.setAttribute('download', id+'.jpg');
        document.body.appendChild(fURL);

        fURL.click();
      });
    },

    downloadContentMarker(path,type,name,id) {
      if (type==="text"){
        axios({
          method: 'get',
          url: '/api/marker/' + id + '/export-marker-text',
          responseType: 'arraybuffer',
        }).then(function (response) {
          let blob = new Blob([response.data], {type: 'application/pdf'});
          let link = document.createElement('a');
          link.href = window.URL.createObjectURL(blob);
          link.download = name+'.pdf' ;
          link.click();
        })

      }else{
        axios({
          url:window.location.origin+'/'+ path,
          method: 'GET',
          responseType: 'blob',
        }).then((response) => {
          var fileURL = window.URL.createObjectURL(new Blob([response.data]));
          var fURL = document.createElement('a');

          fURL.href = fileURL;
          let name_file =  path.substring(path.lastIndexOf('/')+1);
          fURL.setAttribute('download',name_file);
          document.body.appendChild(fURL);

          fURL.click();
        });
      }

    },

    getResults() {
      let route="/api/group/"+this.$route.params.id;
      axios.get(route).then(response => {
          this.group = response.data;
        });
    },

    editModal(marker){
      this.editmode = true;
      this.deleteClassModal();
      this.formGroup.reset();
      $('#addNew2').modal('show');
      this.formGroup.fill(marker);
    },

    updateGroup(){
      this.busy=true;
      this.$Progress.start();
      this.formFile.append('wordDoc', this.wordDoc);
      this.formFile.append('name', this.formGroup.name);
      this.formFile.append('description', this.formGroup.description);
      //this.formFile.append('updated_at', this.form.updated_at);
      this.formFile.append('_method', 'PATCH')

      axios.post('/api/group/'+this.formGroup.id,this.formFile).then((data)=>{
        if(data.data.success){
          swal.fire({
            type: 'success',
            title: data.data.message
          });

          Fire.$emit('AfterCreate');
          $('#addNew2').modal('hide');

          this.$refs.uploadWord.reset();
          this.wordDoc=[];
          this.formFile= new FormData;
          this.formGroup.reset();

        }else{
          swal.fire(
            data.data.data,
            data.data.message,
            "error"
          );

          this.$refs.uploadWord.reset();
          this.wordDoc=[];
          this.formFile= new FormData;
          this.formGroup.reset();

          Fire.$emit('AfterCreate');
          $('#addNew').modal('hide')

          this.$Progress.finish();
          this.busy=false;
        }
      }).catch((error)=>{
        if (error.response.status == 422){
          this.formGroup.errors.errors = error.response.data.errors;
          this.busy=false;
        }

      })


    },



  },

  watch: {
    markersSearch: _.debounce(function(addr) { this.autoComplete(addr) }, 500)
  },

  created() {
    this.getResults();

    Fire.$on('AfterCreate',() => {
      this.getResults();
    });

  },

}
</script>

<style scoped lang="scss">
.imagePreviewWrapper {
  width: 250px;
  height: 250px;
  display: block;
  cursor: pointer;
  margin: 0 auto 30px;
  background-size: cover;
  background-position: center center;
}
</style>
