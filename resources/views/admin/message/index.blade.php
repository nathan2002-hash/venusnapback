@extends('layouts.messageadmin')

@section('title')
    Chats
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar - Conversation List -->
        <div class="col-md-4 bg-light border-right">
            <div class="p-3 border-bottom">
                <h5 class="mb-0">Conversations</h5>
                <div class="mt-3">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="conversationType" id="type-all" checked>
                        <label class="btn btn-outline-primary" for="type-all">All</label>

                        <input type="radio" class="btn-check" name="conversationType" id="type-whatsapp">
                        <label class="btn btn-outline-success" for="type-whatsapp">WhatsApp</label>

                        <input type="radio" class="btn-check" name="conversationType" id="type-sms">
                        <label class="btn btn-outline-info" for="type-sms">SMS</label>
                    </div>
                </div>
            </div>

            <div class="conversation-list" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                @foreach($conversations as $conversation)
                    <div class="conversation-item p-3 border-bottom"
                         data-conversation-id="{{ $conversation->id }}"
                         data-type="{{ $conversation->type }}"
                         style="cursor: pointer;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $conversation->user_number }}</h6>
                                <p class="mb-1 text-muted small conversation-preview">
                                    {{ \Illuminate\Support\Str::limit($conversation->latestMessage->text ?? 'No messages', 50) }}
                                </p>
                                <span class="badge bg-{{ $conversation->type === 'whatsapp' ? 'success' : 'info' }}">
                                    {{ strtoupper($conversation->type) }}
                                </span>
                                @if($conversation->messages_count > 0)
                                    <small class="text-muted ms-2">{{ $conversation->messages_count }} messages</small>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $conversation->latestMessage ? $conversation->latestMessage->created_at->diffForHumans() : $conversation->updated_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="col-md-8">
            <div class="d-flex flex-column" style="height: 100vh;">
                <!-- Chat Header -->
                <div class="p-3 border-bottom bg-white">
                    <div id="chat-header" class="d-none">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 id="current-user-number" class="mb-0"></h5>
                                <span id="current-conversation-type" class="badge"></span>
                            </div>
                            <div class="message-type-toggle">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="messageTypeToggle">
                                    <label class="form-check-label" for="messageTypeToggle">
                                        <span id="toggle-label">WhatsApp</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="no-conversation-selected" class="text-center text-muted">
                        <i class="fas fa-comments fa-3x mb-2"></i>
                        <p>Select a conversation to start chatting</p>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-grow-1 p-3" style="overflow-y: auto; background-color: #f8f9fa;">
                    <div id="messages-container">
                        <!-- Messages will be loaded here via AJAX -->
                    </div>
                </div>

                <!-- Message Input Area -->
                <div class="p-3 border-top bg-white">
                    <div id="message-input-area" class="d-none">
                        <form id="send-message-form">
                            @csrf
                            <input type="hidden" id="conversation_id" name="conversation_id">
                            <input type="hidden" id="message_type" name="message_type" value="whatsapp">

                            <div class="input-group">
                                <textarea id="message-text" name="message" class="form-control"
                                          placeholder="Type your message here..." rows="2"
                                          style="resize: none;"></textarea>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </div>

                            <!-- SMS Character Counter -->
                            <div id="sms-features" class="mt-2 d-none">
                                <small class="text-muted">
                                    <span id="char-count">0</span>/160 characters
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Pusher -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>

<script>
// Initialize Pusher
const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
    cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
    encrypted: true
});

$(document).ready(function() {
    let currentConversationId = null;
    let currentChannel = null;

    // Filter conversations by type
    $('input[name="conversationType"]').change(function() {
        const type = $(this).attr('id').replace('type-', '');
        filterConversations(type);
    });

    function filterConversations(type) {
        $('.conversation-item').each(function() {
            const itemType = $(this).data('type');
            if (type === 'all' || type === itemType) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Select conversation
    $('.conversation-item').click(function() {
        $('.conversation-item').removeClass('active');
        $(this).addClass('active');

        currentConversationId = $(this).data('conversation-id');
        const conversationType = $(this).data('type');

        loadConversation(currentConversationId, conversationType);
    });

    function loadConversation(conversationId, type) {
        // Show chat interface
        $('#no-conversation-selected').addClass('d-none');
        $('#chat-header, #message-input-area').removeClass('d-none');

        // Update header
        const userNumber = $(`.conversation-item[data-conversation-id="${conversationId}"] h6`).text();
        $('#current-user-number').text(userNumber);
        $('#current-conversation-type').text(type.toUpperCase())
            .removeClass('badge-success badge-info')
            .addClass(type === 'whatsapp' ? 'badge-success' : 'badge-info');

        // Set form values
        $('#conversation_id').val(conversationId);
        $('#message_type').val(type);

        // Toggle features based on type
        toggleMessageFeatures(type);

        // Load messages
        loadMessages(conversationId);

        // Subscribe to Pusher channel for real-time updates
        subscribeToConversation(conversationId);
    }

    function toggleMessageFeatures(type) {
        if (type === 'whatsapp') {
            $('#sms-features').hide();
            $('#toggle-label').text('WhatsApp');
            $('#messageTypeToggle').prop('checked', true);
        } else {
            $('#sms-features').show();
            $('#toggle-label').text('SMS');
            $('#messageTypeToggle').prop('checked', false);
        }
    }

    // Message type toggle
    $('#messageTypeToggle').change(function() {
        const isWhatsApp = $(this).is(':checked');
        const newType = isWhatsApp ? 'whatsapp' : 'sms';

        $('#message_type').val(newType);
        $('#toggle-label').text(isWhatsApp ? 'WhatsApp' : 'SMS');
        toggleMessageFeatures(newType);
    });

    // Character count for SMS
    $('#message-text').on('input', function() {
        const count = $(this).val().length;
        $('#char-count').text(count);

        if (count > 160) {
            $('#char-count').addClass('text-danger');
        } else {
            $('#char-count').removeClass('text-danger');
        }
    });

    // Load messages via AJAX
    function loadMessages(conversationId) {
        $.ajax({
            url: '{{ route("admin.chat.messages") }}',
            method: 'GET',
            data: { conversation_id: conversationId },
            success: function(response) {
                $('#messages-container').html(response.html);
                scrollToBottom();
            },
            error: function(xhr) {
                console.error('Error loading messages:', xhr);
            }
        });
    }

    // Subscribe to Pusher channel
    function subscribeToConversation(conversationId) {
        // Unsubscribe from previous channel
        if (currentChannel) {
            currentChannel.unbind('MessageSent');
        }

        // Subscribe to new channel
        currentChannel = pusher.subscribe('private-conversation.' + conversationId);

        currentChannel.bind('MessageSent', function(data) {
            // Add new message to the chat
            addMessageToChat(data.message);
        });
    }

    // Add message to chat UI
    function addMessageToChat(messageData) {
        const messageHtml = `
            <div class="d-flex mb-3 ${messageData.direction === 'inbound' ? 'justify-content-start' : 'justify-content-end'}">
                <div class="message-bubble p-3 ${messageData.direction === 'inbound' ? 'message-inbound' : 'message-outbound'}">
                    <div class="message-text">${messageData.text}</div>
                    <small class="d-block mt-1 opacity-75">
                        ${new Date(messageData.created_at).toLocaleTimeString()}
                        ${messageData.direction === 'outbound' ? '<i class="fas fa-check-double text-success ms-1"></i>' : ''}
                    </small>
                </div>
            </div>
        `;

        $('#messages-container').append(messageHtml);
        scrollToBottom();

        // Update conversation list preview
        updateConversationPreview(messageData);
    }

    // Update conversation preview in sidebar
    function updateConversationPreview(messageData) {
        const conversationItem = $(`.conversation-item[data-conversation-id="${currentConversationId}"]`);
        if (conversationItem.length) {
            conversationItem.find('.conversation-preview').text(
                messageData.text.length > 50 ? messageData.text.substring(0, 50) + '...' : messageData.text
            );
            conversationItem.find('small.text-muted').text('Just now');
        }
    }

    // Send message
    $('#send-message-form').submit(function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const messageText = $('#message-text').val().trim();

        if (!messageText) {
            alert('Please enter a message');
            return;
        }

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: '{{ route("admin.chat.send") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send');

                if (response.success) {
                    $('#message-text').val('');
                    // Message will be added via Pusher event
                } else {
                    alert('Error sending message: ' + response.error);
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send');
                console.error('Error sending message:', error);
                alert('Error sending message. Please check your Vonage credentials and try again.');
            }
        });
    });

    function scrollToBottom() {
        const container = $('#messages-container');
        container.scrollTop(container[0].scrollHeight);
    }
});
</script>

<style>
.conversation-item.active {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.conversation-item:hover {
    background-color: #f8f9fa;
}

.conversation-preview {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}

.message-bubble {
    max-width: 70%;
    word-wrap: break-word;
}

.message-inbound {
    background-color: #e9ecef;
    border-radius: 18px 18px 18px 0;
}

.message-outbound {
    background-color: #007bff;
    color: white;
    border-radius: 18px 18px 0 18px;
}
</style>
@endsection
