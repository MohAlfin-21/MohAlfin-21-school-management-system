<form method="POST" action="{{ route('locale.switch') }}" {{ $attributes->merge(['class' => 'lang-toggle']) }} aria-label="{{ __('ui.language.label') }}">
    @csrf
    <button type="submit" name="locale" value="id" class="lang-btn {{ app()->getLocale() === 'id' ? 'lang-btn-active' : '' }}">ID</button>
    <button type="submit" name="locale" value="en" class="lang-btn {{ app()->getLocale() === 'en' ? 'lang-btn-active' : '' }}">EN</button>
</form>
