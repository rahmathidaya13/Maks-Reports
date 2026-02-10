import { ref } from "vue";
import { router } from "@inertiajs/vue3";
export function useNavigation() {
    const loaderRef = ref(null);
    const navigateTo = (
        routeName,
        params = {},
        message = "Sedang membuka..."
    ) => {
        loaderRef.value?.show(message);
        router.get(
            route(routeName, params),
            {},
            {
                onFinish: () => loaderRef.value?.hide(),
            }
        );
    };

    return {
        loaderRef,
        navigateTo,
    };
}
