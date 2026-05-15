<div class="col-auto"><button class="btn btn-link btn-square" type="button" onclick="closeSearch()"><i
                            data-feather="arrow-left"></i></button></div>
<div class="col"><input class="form-control pe-0 border-0" type="search"
        placeholder="Type something here..."></div>
<div class="col-auto">
    <div class="dropdown input-group-text border-0 p-0"><button
            class="dropdown-toggle btn btn-link btn-square no-caret" type="button" id="searchfilter2"
            data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="sliders"></i></button>
        <div class="dropdown-menu dropdown-menu-end dropdown-dontclose width-300">
            <ul class="nav adminuiux-nav" id="searchtab2" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active"
                        id="searchall-tab2" data-bs-toggle="tab" data-bs-target="#searchall2"
                        type="button" role="tab" aria-controls="searchall2"
                        aria-selected="true">All</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="searchorders-tab2"
                        data-bs-toggle="tab" data-bs-target="#searchorders2" type="button" role="tab"
                        aria-controls="searchorders2" aria-selected="false"
                        tabindex="-1">Orders</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link"
                        id="searchcontacts-tab2" data-bs-toggle="tab" data-bs-target="#searchcontacts2"
                        type="button" role="tab" aria-controls="searchcontacts2" aria-selected="false"
                        tabindex="-1">Contacts</button></li>
            </ul>
            <div class="tab-content py-3" id="searchtabContent">
                <div class="tab-pane fade active show" id="searchall2" role="tabpanel"
                    aria-labelledby="searchall-tab2">
                    <ul class="list-group adminuiux-list-group list-group-flush bg-none show">
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">Search apps</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch1"> <label
                                            class="form-check-label" for="searchswitch1"></label></div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">Include Pages</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch2" checked="">
                                        <label class="form-check-label" for="searchswitch2"></label>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">Internet resource</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch3" checked="">
                                        <label class="form-check-label" for="searchswitch3"></label>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">News and Blogs</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch4"> <label
                                            class="form-check-label" for="searchswitch4"></label></div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="tab-pane fade" id="searchorders2" role="tabpanel"
                    aria-labelledby="searchorders-tab2">
                    <ul class="list-group adminuiux-list-group list-group-flush bg-none show">
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">Show order ID</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch5"> <label
                                            class="form-check-label" for="searchswitch5"></label></div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">International Order</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch6" checked="">
                                        <label class="form-check-label" for="searchswitch6"></label>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">Taxable Product</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch7" checked="">
                                        <label class="form-check-label" for="searchswitch7"></label>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">Published Product</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch8"> <label
                                            class="form-check-label" for="searchswitch8"></label></div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="tab-pane fade" id="searchcontacts2" role="tabpanel"
                    aria-labelledby="searchcontacts-tab2">
                    <ul class="list-group adminuiux-list-group list-group-flush bg-none show">
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">Have email ID</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch9"> <label
                                            class="form-check-label" for="searchswitch9"></label></div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">Have phone number</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch10"
                                            checked=""> <label class="form-check-label"
                                            for="searchswitch10"></label></div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">Photo available</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch11"
                                            checked=""> <label class="form-check-label"
                                            for="searchswitch11"></label></div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col">Referral</div>
                                <div class="col-auto">
                                    <div class="form-check form-switch"><input class="form-check-input"
                                            type="checkbox" role="switch" id="searchswitch12"> <label
                                            class="form-check-label" for="searchswitch12"></label></div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="">
                <div class="row">
                    <div class="col"><button class="btn btn-link">Reset</button></div>
                    <div class="col-auto"><button class="btn btn-theme">Apply</button></div>
                </div>
            </div>
        </div>
    </div>
</div>
