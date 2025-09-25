@if($messages->count() > 0)
    @foreach($messages as $message)
        <div class="d-flex mb-3 {{ $message->direction === 'inbound' ? 'justify-content-start' : 'justify-content-end' }}">
            <div class="message-bubble p-3 {{ $message->direction === 'inbound' ? 'message-inbound' : 'message-outbound' }}">
                <div class="message-text">{{ $message->text }}</div>
                <small class="d-block mt-1 opacity-75">
                    {{ $message->created_at->format('g:i A') }}
                    @if($message->direction === 'outbound')
                        <i class="fas fa-check{{ $message->message_id ? '-double text-success' : '' }} ms-1"></i>
                    @endif
                </small>
            </div>
        </div>
    @endforeach
@else
    <div class="text-center text-muted py-5">
        <i class="fas fa-comments fa-2x mb-2"></i>
        <p>No messages yet. Start the conversation!</p>
    </div>
@endif
