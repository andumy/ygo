<div class="p-10 text-stone-700">
    <div class="pb-10">
        <h1 class="text-2xl font-bold">
            YU-GI-OH! Sets and instances
        </h1>
    </div>
    <div class="flex justify-between">
        <div>
            <h2 class="text-lg font-bold text-cyan-400">
                Recommended sets
            </h2>
            <table>
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Set</th>
                    <th>Total</th>
                    <th>Owned</th>
                    <th>Missing</th>
                </tr>
                </thead>
                <tbody>
                @foreach($recommendedSets as $set)
                    <tr class="py-2">
                        <td>{{$set["code"]}}</td>
                        <td>{{$set["name"]}}</td>
                        <td>{{$set["total"]}}</td>
                        <td>{{$set["owned"]}}</td>
                        <td class="color-red">{{$set["missing"]}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div>
            <h2 class="text-lg font-bold text-cyan-400 pt-5">
                Close to completion sets
            </h2>
            <table>
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Set</th>
                    <th>Total</th>
                    <th>Owned</th>
                    <th>Missing</th>
                </tr>
                </thead>
                <tbody>
                @foreach($completionSets as $set)
                    <tr class="py-2">
                        <td>{{$set["code"]}}</td>
                        <td>{{$set["name"]}}</td>
                        <td>{{$set["total"]}}</td>
                        <td>{{$set["setOwned"]}}</td>
                        <td class="color-red">{{$set["setMissing"]}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
