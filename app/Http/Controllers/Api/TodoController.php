<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TodoController extends Controller
{
    use ResponseTrait;

    /**
     * @OA\Get(
     *      path="/todos",
     *      operationId="getTodosList",
     *      tags={"Todos"},
     *      summary="Get list of todos",
     *      description="Returns paginated list of todos",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Number of items per page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              default=10
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              default=1
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Todos retrieved successfully!"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="data",
     *                      type="array",
     *                      @OA\Items(ref="/app/Models/Todo")
     *                  ),
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="per_page", type="integer", example=10),
     *                  @OA\Property(property="total", type="integer", example=50),
     *                  @OA\Property(property="last_page", type="integer", example=5)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Something went wrong")
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $todos = Todo::orderBy('created_at','desc')->paginate($perPage);

            return $this->sendResponse('Todos retrieved successfully!', $todos);
        } catch (\Exception $exception) {
            Log::error("ERROR RETRIEVING TODOS: " . $exception->getMessage());
            return $this->sendError('Something went wrong', 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/todos",
     *      operationId="storeTodo",
     *      tags={"Todos"},
     *      summary="Create new todo",
     *      description="Store a newly created todo in storage",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="title",
     *                      type="string",
     *                      example="Complete project documentation"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      example="Write comprehensive documentation for the todo API project"
     *                  ),
     *                  @OA\Property(
     *                      property="file",
     *                      type="string",
     *                      format="binary",
     *                      description="PDF file (optional)"
     *                  ),
     *                  required={"title", "description"}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Todo created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="todo added successfully!")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation Error"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="title",
     *                      type="array",
     *                      @OA\Items(type="string", example="The title field is required.")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Something went wrong")
     *          )
     *      )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'file' => 'sometimes|mimes:pdf'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            DB::beginTransaction();

            $todo = new Todo();
            $todo->title = $request->title;
            $todo->description = $request->description;

            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('uploads', ['disk' => 'public']);
                $todo->file_path = $path;
            }

            $todo->save();

            DB::commit();
            return $this->sendSuccess('todo added successfully!');
        } catch (\Exception $exception) {
            Log::error("ERROR ADDING TODO: " . $exception->getMessage());
            return $this->sendError('Something went wrong', 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/todos/{id}",
     *      operationId="getTodoById",
     *      tags={"Todos"},
     *      summary="Get specific todo",
     *      description="Returns a specific todo by ID",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Todo ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Todo retrieved successfully!"),
     *              @OA\Property(property="data", ref="/app/Models/Todo")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Todo not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Todo not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Something went wrong")
     *          )
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $todo = Todo::find($id);

            if (!$todo) {
                return $this->sendError('Todo not found', 404);
            }

            return $this->sendResponse('Todo retrieved successfully!', $todo);
        } catch (\Exception $exception) {
            Log::error("ERROR RETRIEVING TODO: " . $exception->getMessage());
            return $this->sendError('Something went wrong', 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/todos/{id}",
     *      operationId="updateTodo",
     *      tags={"Todos"},
     *      summary="Update existing todo",
     *      description="Update the specified todo in storage",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Todo ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="title",
     *                      type="string",
     *                      example="Updated project documentation"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      example="Updated comprehensive documentation for the todo API project"
     *                  ),
     *                  @OA\Property(
     *                      property="file",
     *                      type="string",
     *                      format="binary",
     *                      description="PDF file (optional)"
     *                  ),
     *                  @OA\Property(
     *                      property="_method",
     *                      type="string",
     *                      example="PUT",
     *                      description="HTTP method override for form data"
     *                  ),
     *                  required={"title", "description"}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Todo updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Todo updated successfully!"),
     *              @OA\Property(property="data", ref="/app/Models/Todo")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation Error"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="title",
     *                      type="array",
     *                      @OA\Items(type="string", example="The title field is required.")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Todo not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Todo not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Something went wrong")
     *          )
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'file' => 'sometimes|mimes:pdf'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            DB::beginTransaction();

            $todo = Todo::find($id);

            if (!$todo) {
                return $this->sendError('Todo not found', 404);
            }

            $todo->title = $request->title;
            $todo->description = $request->description;

            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($todo->getRawOriginal('file_path') && Storage::disk('public')->exists($todo->getRawOriginal('file_path'))) {
                    Storage::disk('public')->delete($todo->getRawOriginal('file_path'));
                }

                // Store new file
                $path = $request->file('file')->store('uploads', ['disk' => 'public']);
                $todo->file_path = $path;
            }

            $todo->save();

            DB::commit();
            return $this->sendResponse('Todo updated successfully!', $todo);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("ERROR UPDATING TODO: " . $exception->getMessage());
            return $this->sendError('Something went wrong', 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/todos/{id}",
     *      operationId="deleteTodo",
     *      tags={"Todos"},
     *      summary="Delete todo",
     *      description="Delete the specified todo from storage",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Todo ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Todo deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Todo deleted successfully!")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Todo not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Todo not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Something went wrong")
     *          )
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $todo = Todo::find($id);

            if (!$todo) {
                return $this->sendError('Todo not found', 404);
            }

            // Delete file if exists
            if ($todo->getRawOriginal('file_path') && Storage::disk('public')->exists($todo->getRawOriginal('file_path'))) {
                Storage::disk('public')->delete($todo->getRawOriginal('file_path'));
            }

            $todo->delete();

            DB::commit();
            return $this->sendSuccess('Todo deleted successfully!');
        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::error("ERROR DELETING TODO: " . $exception->getMessage());
            return $this->sendError('Something went wrong', 500);
        }
    }
}

/**
 * @OA\Schema(
 *     schema="Todo",
 *     type="object",
 *     title="Todo",
 *     description="Todo model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Todo ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Todo title",
 *         example="Complete project documentation"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Todo description",
 *         example="Write comprehensive documentation for the todo API project"
 *     ),
 *     @OA\Property(
 *         property="file_path",
 *         type="string",
 *         nullable=true,
 *         description="Path to uploaded file",
 *         example="uploads/document.pdf"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation timestamp",
 *         example="2024-01-01T00:00:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Last update timestamp",
 *         example="2024-01-01T00:00:00.000000Z"
 *     )
 * )
 */
