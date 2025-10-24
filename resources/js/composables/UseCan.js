import { usePage } from "@inertiajs/vue3";
export default function useCan() {
    const page = usePage();
    return function (actions) {
        return page.props.permissions?.can[actions] ?? false;
    };
}
