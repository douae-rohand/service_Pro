import { onMounted, onUnmounted, ref } from 'vue';

export function useSse() {
    const eventSource = ref(null);
    const isConnected = ref(false);

    /**
     * Initialize SSE connection
     * @param {string} url - The URL to connect to (e.g., /api/sse/stream?type=admin)
     * @param {Object} eventHandlers - Map of event names to callback functions
     */
    const initSse = (url, eventHandlers = {}) => {
        // Close existing connection if any
        if (eventSource.value) {
            eventSource.value.close();
        }

        // Add base URL if needed (assuming Vite proxy or full URL)
        // If using Vite proxy, '/api/...' is fine. If not, might need import.meta.env.VITE_API_URL
        const fullUrl = url.startsWith('http') ? url : `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'}${url}`;

        try {
            eventSource.value = new EventSource(fullUrl);

            eventSource.value.onopen = () => {
                // console.log('SSE Connected to', url);
                isConnected.value = true;
            };

            eventSource.value.onerror = (err) => {
                // console.error('SSE Error:', err);
                isConnected.value = false;
                // EventSource automatically attempts to reconnect, so we don't need manual retry mostly
            };

            // Register default listeners
            eventSource.value.addEventListener('connected', (event) => {
                // console.log('SSE System Message:', JSON.parse(event.data).message);
            });

            // Register custom event listeners
            for (const [eventName, handler] of Object.entries(eventHandlers)) {
                eventSource.value.addEventListener(eventName, (event) => {
                    try {
                        const data = JSON.parse(event.data);
                        handler(data);
                    } catch (e) {
                        console.error(`Error parsing SSE data for event ${eventName}:`, e);
                    }
                });
            }

        } catch (e) {
            console.error('Failed to initialize SSE:', e);
            isConnected.value = false;
        }
    };

    const closeSse = () => {
        if (eventSource.value) {
            eventSource.value.close();
            eventSource.value = null;
            isConnected.value = false;
        }
    };

    onUnmounted(() => {
        closeSse();
    });

    return {
        initSse,
        closeSse,
        isConnected
    };
}
