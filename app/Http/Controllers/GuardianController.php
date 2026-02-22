<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\User;
use App\Repositories\User\UserInterface;
use App\Repositories\ClassSchool\ClassSchoolInterface;
use App\Repositories\Section\SectionInterface;
use App\Repositories\ClassSection\ClassSectionInterface;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use App\Services\UploadService;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class GuardianController extends Controller {
    protected UserInterface $user;
    private ClassSchoolInterface $class;
    private SectionInterface $section;
    private ClassSectionInterface $classSection;

    public function __construct(UserInterface $user, ClassSchoolInterface $class, SectionInterface $section, ClassSectionInterface $classSection) {
        $this->user = $user;
        $this->class = $class;
        $this->section = $section;
        $this->classSection = $classSection;
    }

    public function index() {
        ResponseService::noPermissionThenRedirect('guardian-list');
        $classes = $this->class->all(['id', 'name', 'medium_id'], ['stream', 'medium']);
        $sections = $this->section->builder()->orderBy('name', 'ASC')->get();

        $class_sections = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);

        // dd($class_sections );
        return view('guardian.index', compact('classes','class_sections'));
    }

    public function store(Request $request) {
        ResponseService::noPermissionThenRedirect('guardian-create');
        $request->validate([
            'first_name' => 'required',
            'email'      => 'required|unique:users,email',
            'last_name'  => 'required',
            'gender'     => 'required',
            'mobile'     => 'required',
        ]);
        try {
            DB::beginTransaction();
            $guardian = $this->user->create($request->all());
            $guardian->assignRole('Guardian');
            DB::commit();
            ResponseService::successResponse('Data Created Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Guardian Controller -> Store method");
            ResponseService::errorResponse();
        }
    }

    public function show(Request $request) {
        // dd($request->all());
        ResponseService::noPermissionThenRedirect('guardian-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');

        $sql = $this->user->guardian()->with(['child.class_section', 'guardians']);

        if($request->class_id && $request->class_id != 'all')
        {
            $sql->whereHas('child.class_section', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if($request->class_section_id && $request->class_section_id != 'all')
        {
            $sql->whereHas('child', function ($q) use ($request) {
                $q->where('class_section_id', $request->class_section_id);
            });
        }

        $sql = $sql->owner();

        if (!empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where(function ($query) use ($search) {
                $query->where('id', 'LIKE', "%$search%")->orwhere('first_name', 'LIKE', "%$search%")
                    ->orwhere('last_name', 'LIKE', "%$search%")->orwhere('gender', 'LIKE', "%$search%")
                    ->orwhere('email', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
            });
        }
        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();
        // dd($res);
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($res as $row) {
            $operate = BootstrapTableService::editButton(route('guardian.update', $row->id));
            $operate .= '<button
                class="btn btn-xs btn-rounded btn-icon btn-gradient-info add-more-guardian"
                data-user-id="'.$row->id.'"
                data-toggle="modal"
                data-target="#addMoreGuardianModal"
                title="Add More Guardian">
                <i class="fa fa-plus"></i>
            </button>&nbsp;';
            $tempRow = $row->toArray();
            $guardianDetails = $row->guardians->map(function ($g) {
                return '
                    <span class="guardian-item" data-id="'.$g->id.'"
                        data-name="'.e($g->name).'"
                        data-mobile="'.e($g->mobile).'">
                        '.$g->name.' ('.$g->mobile.')
                        <a href="javascript:void(0)"
                        class="edit-guardian text-primary ml-1"
                        title="Edit Guardian">
                            <i class="fa fa-edit"></i>
                        </a>
                    </span>
                ';
            })->implode('<br>');

            $tempRow['more'] = $guardianDetails ?: '-';
            $tempRow['no'] = $no++;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function update(Request $request) {
        ResponseService::noPermissionThenSendJson('guardian-edit');
        $request->validate([
            'edit_id'    => 'required',
            'first_name' => 'required',
            'email'      => 'required|unique:users,email,' . $request->edit_id,
            'last_name'  => 'required',
            'gender'     => 'required',
            'mobile'     => 'required',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp',
        ]);
        try {
            $data = $request->except('_token', 'edit_id', '_method','reset_password');
            $guardian = $this->user->guardian()->where('id', $request->edit_id)->firstOrFail();
            if (!empty($request->image)) {
                if ($guardian->image) {
                    UploadService::delete($guardian->getRawOriginal('image'));
                }
                $data['image'] = UploadService::upload($request->image, 'guardian');
            }

            if ($request->reset_password) {
                $data['password'] = Hash::make($request->mobile);
            }

            $this->user->guardian()->where('id', $request->edit_id)->update($data);
            $guardian->assignRole('Guardian');
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Guardian Controller -> Update method");
            ResponseService::errorResponse();
        }
    }

    public function search(Request $request) {
        ResponseService::noAnyPermissionThenSendJson(['student-create', 'student-edit']);
        $parent = $this->user->guardian()->where(function ($query) use ($request) {
            $query->where('email', 'like', '%' . $request->email . '%')
                ->orWhere('first_name', 'like', '%' . $request->email . '%')
                ->orWhere('last_name', 'like', '%' . $request->email . '%');
        })->get();

        if (!empty($parent)) {
            $response = [
                'error' => false,
                'data'  => $parent
            ];
        } else {
            $response = [
                'error'   => true,
                'message' => trans('no_data_found')
            ];
        }
        return response()->json($response);
    }

    public function searchAjax(Request $request)
    {
        ResponseService::noAnyPermissionThenSendJson(['student-create', 'student-edit']);

        $query = trim($request->input('query'));

        $users = User::where(function ($q) use ($query) {
                $q->where('email', 'like', "%{$query}%")
                ->orWhere('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->with('student')
            ->limit(10)
            ->get();
        // dd($users);
        return view('partials.user_search_results', compact('users'));
    }

    public function addNew(Request $request)
    {
        ResponseService::noPermissionThenSendJson('guardian-create');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name'    => 'required',
            'mobile'  => 'required'
        ]);

        DB::table('guardians')->insert([
            'user_id'    => $request->user_id,
            'name'       => $request->name,
            'mobile'     => $request->mobile,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function updateMore(Request $request)
    {
        $request->validate([
            'guardian_id' => 'required|exists:guardians,id',
            'name' => 'required',
            'mobile' => 'required'
        ]);

        Guardian::where('id', $request->guardian_id)
            ->update([
                'name' => $request->name,
                'mobile' => $request->mobile,
            ]);

        return response()->json(['status' => true]);
    }
}
