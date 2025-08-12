<div class="tabbable-custom nav-justified">
    <ul class="nav nav-tabs nav-justified">
        <li class="active">
            <a href="#tab_1" data-toggle="tab"></a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab_1">
            <table class="table table-responsive table-light table-bordered">
                <tbody data-bind="foreach: Entity.Sheets">
                    <tr>
                        <td class="col-md-4vertical-align-middle" data-bind="text: type_name()">
                        </td>
                        <td class="col-md-5 vertical-align-middle">

                            <input id="input-700" data-bind="fileinput: $data, fileinputOptions: {
                                uploadUrl: '/media/file/upload',
                                initialCaption: filename() ? filename() : [],
                                uploadExtraData: {
                                    UserToken: User.Token,
                                    path: $parent.FilePath(),
                                },
                                maxFileSize: 100 * 1024,
                                initialPreview: filename() ? [$parent.FilePath() + filename()] : [],
                                allowedFileExtensions: ['jpg', 'jpeg', 'png', 'pdf', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'dwg']
                            }" name="file[]" type="file">

                        </td>
                        <td class="col-md-3 text-center vertical-align-middle">
                            <!-- ko if: filename() -->
                            <a data-bind="click: $root.downloadFile.bind($data, filename(), 'concurso', $root.Entity.Id())"
                                download class="btn btn-xl green" title="Descargar">
                                Descargar
                                <i class="fa fa-download"></i>
                            </a>
                            <!-- /ko -->
                            <!-- ko if: !filename() -->
                            <span class="label label-danger">Sin archivo</span>
                            <!-- /ko -->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>