export class A {
	parent?: string;

	constructor(attrs: object = {}) {
		Object.assign(this, attrs);
	}
}

 class B extends A {
	public child?: string;
}

console.log(new B({parent: "1", child: "2"}));
