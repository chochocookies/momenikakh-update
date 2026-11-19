<section id="people" class="inv-section" aria-labelledby="people-title" data-reveal>
    <h2 id="people-title" class="inv-heading">{{ $invitation->peopleHeading() }}</h2>
    <hr class="inv-divider">
    <ul class="inv-people" role="list">
        @foreach ($invitation->people as $person)
            <li class="inv-person">
                @if ($invitation->photoFor($person))
                    <x-invitation.image :image="$invitation->photoFor($person)" sizes="176px" class="inv-photo" />
                @endif
                <p class="inv-person__role">{{ $invitation->roleLabel($person) }}</p>
                <h3 class="inv-person__name">{{ $person->name }}</h3>
                @if ($person->nickname && $person->nickname !== $person->name)
                    <p class="inv-person__nick">“{{ $person->nickname }}”</p>
                @endif
                @if ($invitation->birthLabel($person))
                    <p class="inv-text">Lahir pada {{ $invitation->birthLabel($person) }}</p>
                @endif
                @if ($person->parentsText)
                    <p class="inv-text">{{ $person->parentsText }}</p>
                @endif
                @if ($person->bio)
                    <p class="inv-text">{{ $person->bio }}</p>
                @endif
                @if ($person->instagram)
                    <a class="inv-link" href="https://instagram.com/{{ $person->instagram }}" target="_blank" rel="noopener noreferrer">{{ '@'.$person->instagram }}<span class="sr-only"> di Instagram (tab baru)</span></a>
                @endif
            </li>
        @endforeach
    </ul>
</section>
