import Collection from "@/components/Collection/index.js";
import Dialogs from "@/components/Dialogs/index.js";
import Drawers from "@/components/Drawers/index.js";
import Dropdowns from "@/components/Dropdowns/index.js";
import Errors from "@/components/Errors/index.js";
import Forms from "@/components/Forms/index.js";
import Lab from "@/components/Lab/index.js";
import Layout from "@/components/Layout/index.js";
import Misc from "@/components/Misc/index.js";
import Navigation from "@/components/Navigation/index.js";
import Sections from "@/components/Sections/index.js";
import Text from "@/components/Text/index.js";
import Uploads from "@/components/Uploads/index.js";
import View from "@/components/View/index.js";
import Views from "@/components/Views/index.js";

export default {
	install(app) {
		app.use(Collection);
		app.use(Dialogs);
		app.use(Drawers);
		app.use(Dropdowns);
		app.use(Errors);
		app.use(Forms);
		app.use(Lab);
		app.use(Layout);
		app.use(Misc);
		app.use(Navigation);
		app.use(Sections);
		app.use(Text);
		app.use(Uploads);
		app.use(View);
		app.use(Views);
	}
};
