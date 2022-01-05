@php
$editing = isset($productType);
@endphp
<div class="flex flex-wrap -mx-4 -mb-4 md:mb-0">
    @php
    $locales = get_all_language();
    if($editing) {
        $name = $productType->translations['name'];
        $description = $productType->translations['description'];
    }
    @endphp

    @foreach ($locales as $key => $value)
    <x-inputs.text name="name[{{ $key }}]" :label="__('crud.inputs.name').' ('.$value.')'" value="{{ old('name['.$key.']', ($editing ? ($name[$key] ?? '') : '')) }}" required></x-inputs.text>
    @endforeach
    <x-inputs.status :status="$editing ? $productType->status : ''"></x-inputs.status>

    @foreach ($locales as $key => $value)
        <x-inputs.textarea space="w-full" :label="__('crud.inputs.description').' ('.$value.')'" name="description[{{ $key }}]" required>
            {{ old('description['.$key.']', ($editing ? ($description[$key] ?? '') : '')) }}
        </x-inputs.textarea>
    @endforeach


    <div class="w-full md:w-1/2 px-4 mb-4 md:mb-0">
        <div class="mb-6">
            <div x-data="imageComponentData()">
                <x-inputs.partials.label name="icon" :label="__('crud.inputs.image')"></x-inputs.partials.label>
                <img :src="imageDataUrl" style="object-fit: cover; width: 150px; height: 150px;" /><br />

                <div class="mt-2">
                    <input class="block dark:text-gray-400 text-gray-800 text-sm font-semibold mb-2" type="file"
                        name="icon" id="icon" @change="fileChanged" accept="image/*" />
                </div>
            </div>
        </div>
    </div>
</div>

@push('endScripts')
<script>
    /* Alpine component for image uploader viewer */
        function imageComponentData() {
            return {
                imageDataUrl: '{{ $editing && $productType->icon ? asset("storage/".$productType->icon) : asset("img/avatar.png") }}',

                fileChanged(event) {
                    this.fileToDataUrl(event, src => this.imageDataUrl = src)
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