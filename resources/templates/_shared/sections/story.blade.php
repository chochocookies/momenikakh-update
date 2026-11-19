<section id="story" class="inv-section" aria-labelledby="story-title" data-reveal>
    <h2 id="story-title" class="inv-heading">Perjalanan Kami</h2>
    <hr class="inv-divider">
    <ol class="inv-timeline" role="list">
        @foreach ($invitation->story as $item)
            <li class="inv-card inv-timeline__item">
                @if ($item->happenedOn)
                    <time class="inv-timeline__date" datetime="{{ $item->happenedOn }}">{{ $invitation->storyDateLabel($item) }}</time>
                @endif
                <h3 class="inv-timeline__title">{{ $item->title }}</h3>
                <p class="inv-text">{{ $item->body }}</p>
            </li>
        @endforeach
    </ol>
</section>
