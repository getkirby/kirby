import AccountView from "./AccountView.vue";
import ResetPasswordView from "./ResetPasswordView.vue";
import UserAvatar from "./UserAvatar.vue";
import UserInfo from "./UserInfo.vue";
import UserProfile from "./UserProfile.vue";
import UserView from "./UserView.vue";
import UsersView from "./UsersView.vue";

import UserSecurityDrawer from "./UserSecurityDrawer.vue";

export default {
	install(app) {
		app.component("k-account-view", AccountView);
		app.component("k-reset-password-view", ResetPasswordView);
		app.component("k-user-avatar", UserAvatar);
		app.component("k-user-info", UserInfo);
		app.component("k-user-profile", UserProfile);
		app.component("k-user-view", UserView);
		app.component("k-users-view", UsersView);

		app.component("k-user-security-drawer", UserSecurityDrawer);
	}
};
