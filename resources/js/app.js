/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';

import { createApp } from 'vue';
import 'vue3-emoji-picker/css';
import trans from './trans';

import ChatComponent from './components/chat/ChatComponent.vue';
import GroupChatComponent from './components/chat/GroupChatComponent.vue';
import NotificationComponent from './components/chat/NotificationComponent.vue';
import JqueryNotificationComponent from './components/chat/JqueryNotificationComponent.vue';
import JqueryChatComponent from './components/chat/JqueryChatComponent.vue';
import JqueryGroupChatComponent from './components/chat/JqueryGroupChatComponent.vue';
import SidePanelComponent from './components/chat/SidePanelComponent.vue';
import StatusChangeComponent from './components/chat/StatusChangeComponent.vue';

/**
 * Keeps a chat container scrolled to the bottom whenever its content changes
 * (replaces the Vue 2 only "vue-chat-scroll" plugin).
 */
const chatScroll = {
    mounted(el) {
        const scrollDown = () => { el.scrollTop = el.scrollHeight; };
        el._chatScrollObserver = new MutationObserver(scrollDown);
        el._chatScrollObserver.observe(el, { childList: true, subtree: true });
        scrollDown();
    },
    unmounted(el) {
        el._chatScrollObserver && el._chatScrollObserver.disconnect();
    }
};

/**
 * Next, we will create the Vue application instances and attach them to
 * the page. Each mount point gets its own app with the same components.
 */
function mountApp(selector) {
    if (!document.querySelector(selector)) {
        return null;
    }

    const app = createApp({});

    app.mixin(trans);
    app.directive('chat-scroll', chatScroll);

    app.component('chat-component', ChatComponent);
    app.component('group-chat-component', GroupChatComponent);
    app.component('notification-component', NotificationComponent);
    app.component('jquery-notification-component', JqueryNotificationComponent);
    app.component('jquery-chat-component', JqueryChatComponent);
    app.component('jquery-group-chat-component', JqueryGroupChatComponent);
    app.component('side-panel-component', SidePanelComponent);
    app.component('status-change-component', StatusChangeComponent);

    return app.mount(selector);
}

mountApp('#admin-visitor-area');
mountApp('#main-nav-for-chat');
