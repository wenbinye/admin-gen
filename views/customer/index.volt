{% macro thead(columns) %}
<tr>
  {% for column in columns %}
  <th>{{ column.label }}</th>
  {% endfor %}
  <th>Operation</th>
</tr>
{% endmacro %}

<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">Customer</h1>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div id="data-table" class="row">
  <div class="col-lg-12 table-responsive mt20">
    <table class="table table-bordered table-striped">
      <thead>{{ thead(columns) }}</thead>
      <tfoot>{{ thead(columns) }}</tfoot>
    </table>
    <input id="csrf-token" type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}"/>
  </div><!-- /.box -->
  <div class="col-lg-12">
    <button class="btn btn-primary create-btn">New customer</button>
  </div>
  <script class="buttons-tmpl" type="text/x-tmpl">
    <div data-id="{{ '{{' ~ primary_key ~ '}}' }}">
      <button class="btn btn-primary btn-xs edit-btn">Edit</button>
      <button class="btn btn-danger btn-xs delete-btn">Delete</button>
    </div>
  </script>
  <!-- 编辑变量对话框 -->
  <div class="modal fade edit-dialog" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-body">
          <h2></h2>
          <div role="alert"></div>
          <form role="form">
            {% for elem in form %}
            {% if elem.getAttribute('hidden') %}
            {{ elem.render() }}
            {% else %}
            <div class="form-group">
              {{ elem.label() }}
              {{ elem.render() }}
              <p class="help-block">{{ elem.getAttribute('help') }}</p>
              <p class="help-block error-desc hide"></p>
            </div>
            {% endif %}
            {% endfor %}
          </form>
          <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-default">Cancel</button>
            <button type="button" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{% do beginclip('script') %}
<!-- page script -->
<script type="text/javascript">
requirejs(['crud'], function(App) {
    new App().init({
        baseUrl: '{{ url("/customer") }}',
        columns: [
			{% for column in columns %}
            { "data": "{{ column.name }}"}{% if not loop.last %},{% endif %}
            {% endfor %}
        ],
        name: "customer",
        display_column: "name",
        primary_key: "{{ primary_key }}"
    });
});
{% if has_textarea %}
requirejs(['bootstrap.wysihtml5'], function() {
   $(".textarea").wysihtml5()
});
{% endif %}
</script>
{% do endclip() %}
