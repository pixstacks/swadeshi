@php 
    $editing = isset($banner);
@endphp

<div class="flex flex-wrap -mx-4 -mb-4 md:mb-0">
    <div class="flex flex-row w-full px-4 mb-4 md:w-1/2 md:mb-0">
        <div class="mb-6">
            <div x-data="avatarComponentData()">
                <x-inputs.partials.label name="avatar" :label="__('crud.inputs.image')"></x-inputs.partials.label>
                <img :src="avatarDataUrl" style="object-fit: cover; width: 150px; height: 150px;" /><br />
                <div class="mt-2">
                    <input type="file" name="url" id="avatar" @change="fileChanged" />
                </div>
            </div>
        </div>
        {{-- <div class="mb-6">
            <div x-data="avatarComponentData()">
                <x-inputs.partials.label name="video" :label="__('crud.inputs.video')"></x-inputs.partials.label>
                    <img :src="videoDataUrl" style="object-fit: cover; width: 150px; height: 150px;" /><br />
                    <div class="mt-2">
                        <input type="file" name="video" id="video" accept="video/mp4"  />
                    </div>
            </div>
        </div> --}}
    </div>
    <x-inputs.text name="click_url" :label="__('crud.inputs.click_url').' URL'" value="{{ old('reason', ($editing ? $banner->click_url : '')) }}"></x-inputs.text>

    {{-- <x-inputs.text name="video" :label="__('crud.inputs.video').' URL'" value="{{ old('reason', ($editing ? $banner->video : '')) }}"></x-inputs.text> --}}



    <x-inputs.number min="0" name="position" :label="__('crud.inputs.position')" value="{{ old('reason', ($editing ? $banner->position : 0)) }}"></x-inputs.number>

    <x-inputs.status :status="$editing ? $banner->status : ''"></x-inputs.status>
</div>
@push('endScripts')
    <script>
        /* Alpine component for avatar uploader viewer */
        function avatarComponentData() {
            return {
                avatarDataUrl: '{{ $editing && $banner->url ? asset("storage/".$banner->url) : asset("img/avatar.png") }}',

                fileChanged(event) {
                    this.fileToDataUrl(event, src => this.avatarDataUrl = src)
                },

                fileToDataUrl(event, callback) {
                    if (! event.target.files.length) return

                    let file = event.target.files[0],
                        reader = new FileReader()

                    reader.readAsDataURL(file)
                    reader.onload = e => callback(e.target.result)
                }
            }
        }
    </script>
@endpush
