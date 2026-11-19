<x-layouts.public
    :title="$invitation->title.' · '.$invitation->eventLabel"
    :description="'Undangan '.$invitation->eventLabel.' '.$invitation->title"
    :assets="$template->viteEntries()">
    <div class="inv" data-theme="{{ $template->slug }}" data-palette="{{ $invitation->palette }}" data-motion="{{ $invitation->motion }}" data-ambient="{{ $template->motion['ambient'] ?? 'none' }}">
        @foreach ($invitation->sections as $section)
            @invitationSection($section)
        @endforeach
    </div>
</x-layouts.public>
