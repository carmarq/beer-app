import {Component, ViewChild, OnInit} from "@angular/core";
import {Router} from "@angular/router";
import {setTimeout} from "timers";
import {FormBuilder, FormGroup, Validators} from "@angular/forms";

import {Status} from "../../classes/status";
import {SignIn} from "../../classes/sign.in";

import {SignInService} from "../../services/sign.in.service";
import {SessionService} from "../../services/session.service";


declare let $: any;


@Component({
	template: require("./sign-in.component.html"),
	selector: 'sign-in'
})

export class SignInComponent implements OnInit {

	signInForm: FormGroup;

	signIn: SignIn = new SignIn(null, null);
	status: Status = null;

	constructor(
		private formBuilder: FormBuilder,
		private router: Router,
		private signInService: SignInService,
		private sessionService: SessionService
	){}

	ngOnInit(): void {
		this.signInForm = this.formBuilder.group({
				profileEmail: ["", [Validators.maxLength(128), Validators.required]],
				profilePassword: ["", [Validators.maxLength(48), Validators.required]],
			}
		);
		this.applyFormChanges();
	}

	applyFormChanges() :void {
		this.signInForm.valueChanges.subscribe(values => {
			for(let field in values) {
				this.signIn[field] = values[field];
			}
		});
	}

	createSignIn(): void {

		//let signIn = new SignIn(this.signInForm.value.profileEmail, this.signInForm.value.profilePassword);
		this.signInService.postSignIn(this.signIn)
			.subscribe(status => {
				this.status = status;

				if (this.status.status === 200) {
					this.sessionService.setSession();
					this.signInForm.reset();
					location.reload();

					setTimeout(function() {
						$("#signInForm").modal('hide');
					}, 500);

					this.router.navigate(["/brewer-signed-in"]);
				}
			});


	}
	signOut() :void {
		this.signInService.getSignOut();
	}
}