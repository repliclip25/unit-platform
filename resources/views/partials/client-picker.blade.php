{{--
    Searchable client picker
    Variables: $pickerId (unique string), $selectedId (int|string), $selectedName (string)
--}}
<div class="relative">
    <input type="hidden" name="client_id" id="cp-val-{{ $pickerId }}" value="{{ $selectedId }}">
    <input type="text"
        id="cp-search-{{ $pickerId }}"
        data-picker="{{ $pickerId }}"
        value="{{ $selectedName }}"
        placeholder="Search client…"
        autocomplete="off"
        class="cp-search w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-yellow-500 placeholder-gray-600">
    <div id="cp-drop-{{ $pickerId }}"
        class="hidden absolute z-50 top-full left-0 right-0 mt-1 bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-h-52 overflow-y-auto">
    </div>
</div>
