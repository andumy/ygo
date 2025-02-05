<div>
    <form wire:submit.prevent="save">
        <input type="file" wire:model="file">

        @error('photo') <span class="error">{{ $message }}</span> @enderror

        <button type="submit">Bulk upload</button>
    </form>

    <p>{{$cellValue}}</p>
</div>
