@php
$editing = isset($serviceType);
@endphp
<div class="flex flex-wrap -mx-4 -mb-4 md:mb-0">
    @php
    $locales = get_all_language();
    if($editing) {
    $name = $serviceType->translations['name'];
    $description = $serviceType->translations['description'];
    }
    @endphp

    @foreach ($locales as $key => $value)
    <x-inputs.text name="name[{{ $key }}]" :label="__('crud.inputs.name').' ('.$value.')'"
        value="{{ old('name['.$key.']', ($editing ? ($name[$key] ?? '') : '')) }}" required></x-inputs.text>
    <x-inputs.textarea space="w-full" :label="__('crud.inputs.description').' ('.$value.')'"
        name="description[{{ $key }}]" required>
        {{ old('description['.$key.']', ($editing ? ($description[$key] ?? '') : '')) }}
    </x-inputs.textarea>
    @endforeach

    <x-inputs.number name="fixed" step=".01" :label="__('crud.inputs.fixed')"
        value="{{ old('fixed', ($editing ? $serviceType->fixed : '')) }}"></x-inputs.number>

    <x-inputs.number name="price" step=".01" :label="__('crud.inputs.price')"
        value="{{ old('price', ($editing ? $serviceType->price : '')) }}"></x-inputs.number>

    <x-inputs.number name="type_price" step=".01" :label="__('crud.inputs.type_price')"
        value="{{ old('type_price', ($editing ? $serviceType->type_price : '')) }}"></x-inputs.number>

    <x-inputs.select name="calculator" label="{{ __('crud.inputs.calculator') }}">
        <option
            {{ old('calculator', ($editing && $serviceType->calculator ? $serviceType->calculator : '')) == "DEFAULT" ? 'selected' : ''  }}
            value="DEFAULT">Default</option>
        <option
            {{ old('calculator', ($editing && $serviceType->calculator ? $serviceType->calculator : '')) == "FIXED" ? 'selected' : ''  }}
            value="FIXED">Fixed</option>
        <option
            {{ old('calculator', ($editing && $serviceType->calculator ? $serviceType->calculator : '')) == "HOUR" ? 'selected' : ''  }}
            value="HOUR">Hour</option>
        {{-- <option
            {{ old('calculator', ($editing && $serviceType->calculator ? $serviceType->calculator : '')) == "DAILY" ? 'selected' : ''  }}
            value="DAILY">Daily</option>
        <option
            {{ old('calculator', ($editing && $serviceType->calculator ? $serviceType->calculator : '')) == "WEIGHT" ? 'selected' : ''  }}
            value="WEIGHT">Weight</option>
        <option
            {{ old('calculator', ($editing && $serviceType->calculator ? $serviceType->calculator : '')) == "ESTIMATE" ? 'selected' : ''  }}
            value="ESTIMATE">Estimate</option>
        <option
            {{ old('calculator', ($editing && $serviceType->calculator ? $serviceType->calculator : '')) == "PSQFT" ? 'selected' : ''  }}
            value="PSQFT">Psqft</option> --}}
    </x-inputs.select>

    <x-inputs.select name="parent_id" label="{{ __('crud.inputs.parent_id') }}">
        <option hidden value="">Select Parent Id</option>
        <option value="0">no Parent </option>
        @forelse ($serviceTypes as $st)
        @if(($editing && $st->id != $serviceType->id) || !$editing)
        <option {{ (old('parent_id') ? old('parent_id') == $st->id : NULL) ? 'selected' : ''  }} value="{{ $st->id }}">
            {{ $st->name }}</option>
        @endif
        @empty
        No Services Found
        @endforelse
    </x-inputs.select>


    <x-inputs.status :status="$editing ? $serviceType->status : ''"></x-inputs.status>

    <div class="w-full px-4 mb-4 md:w-1/2 md:mb-0">
        <div class="mb-6">
            <div x-data="imageComponentData()">
                <x-inputs.partials.label name="image" :label="__('crud.inputs.image')"></x-inputs.partials.label>
                <img :src="imageDataUrl" style="object-fit: cover; width: 150px; height: 150px;" /><br />

                <div class="mt-2">
                    <input class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-400" type="file"
                        name="image" id="image" @change="fileChanged" accept="image/*" />
                </div>
            </div>
        </div>
    </div>

    <div class="w-full px-4 mb-4 md:w-1/2 md:mb-0">
        <div class="mb-6">
            <div x-data="markerComponentData()">

                <x-inputs.partials.label name="marker" :label="__('crud.inputs.marker')"></x-inputs.partials.label>
                <img :src="markerDataUrl" style="object-fit: cover; width: 150px; height: 150px;" /><br />

                <div class="mt-2">
                    <input class="block mb-2 text-sm font-semibold text-gray-800 dark:text-gray-400" type="file"
                        name="marker" id="marker" accept="image/*" @change="fileChanged" />
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
                imageDataUrl: '{{ $editing && $serviceType->image ? asset("storage/".$serviceType->image) : asset("img/avatar.png") }}',

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
        function markerComponentData() {
            return {
                markerDataUrl: '{{ $editing && $serviceType->marker ? asset("storage/".$serviceType->marker) : asset("img/avatar.png") }}',

                fileChanged(event) {
                    this.fileToDataUrl(event, src => this.markerDataUrl = src)
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