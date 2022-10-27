import Dialogs from "@/components/Dialogs/index.js";
import Drawers from "@/components/Drawers/index.js";
import Forms from "@/components/Forms/index.js";
import Layout from "@/components/Layout/index.js";
import Misc from "@/components/Misc/index.js";
import Navigation from "@/components/Navigation/index.js";
import Sections from "@/components/Sections/index.js";
import Views from "@/components/Views/index.js";
import Blocks from "@/components/Forms/Blocks/index.js";
import Previews from "@/components/Forms/Previews/index.js";

// 3rd party libraries
import Portal from "@linusborg/vue-simple-portal";

export default {
	install(app) {
		app.use(Dialogs);
		app.use(Drawers);
		app.use(Forms);
		app.use(Layout);
		app.use(Misc);
		app.use(Navigation);
		app.use(Sections);
		app.use(Views);
		app.use(Blocks);
		app.use(Previews);

		app.use(Portal);
	}
};
